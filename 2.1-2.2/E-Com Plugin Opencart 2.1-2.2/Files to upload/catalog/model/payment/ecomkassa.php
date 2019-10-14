<?php 
class ModelPaymentEcomkassa extends Model {

	public $token = '';
  	public function checkTransaction($ecom_data ) {

		$status = $this->config->get('ecomkassa_status');
		if($status !=1){
			return;
		}
	
		
		$order_id = $ecom_data['order_info']['order_id'];
		$receipt = $this->getOrderReceipt($order_id);
		if(!empty($receipt)){
			return;
		}
		
		
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id); 

        $order_payment_method = $order_info['payment_code']; 
        $order_status_id = $order_info['order_status_id'];

		$ecomkassa_payment = $this->config->get('ecomkassa_payment');
		if(!isset($ecomkassa_payment[$order_payment_method])){
			return;
		}
	
		if($ecomkassa_payment[$order_payment_method] !=$order_status_id ){
			return;
		}
 
		$shopid = $this->config->get('ecomkassa_shopid');
		$password = $this->config->get('ecomkassa_password');
		$login = $this->config->get('ecomkassa_login');
		$url = $this->config->get('ecomkassa_url');
		
		
		$authToken = $this->getToken();
		$this->sell($order_info, $authToken);
		
  	}
	
	public function getUpdate($receipt){	
	 
		$this->getToken();
 
		$shopid = $this->config->get('ecomkassa_shopid');
		$password = $this->config->get('ecomkassa_password');
		$login = $this->config->get('ecomkassa_login');
		$url =trim(  $this->config->get('ecomkassa_url'), '/').'/'.$shopid. '/report/'.$receipt['uuid'].'?tokenid='.$this->token;  
 
		$response = $this->curlFunction( $url,  array(), false);
		
		$json = json_decode($response);
		if(!$json){
		 
			return;
		}
		
		
		if($json->error == null){
			$receipt['error'] = $json->error;
			$receipt['status'] = $json->status;
			$receipt['fn_number'] = $json->payload->fn_number;
			$receipt['shift_number'] = $json->payload->shift_number;
			$receipt['receipt_datetime'] = $json->payload->receipt_datetime;
			$receipt['fiscal_receipt_number'] = $json->payload->fiscal_receipt_number;
			$receipt['fiscal_document_number'] = $json->payload->fiscal_document_number;
			$receipt['ecr_registration_number'] = $json->payload->ecr_registration_number;
			$receipt['fiscal_document_attribute'] = $json->payload->fiscal_document_attribute;
			$receipt['fns_site'] = $json->payload->fns_site;
			$receipt['timestamp'] = $json->timestamp;
			$receipt['group_code'] = $json->group_code;
			$receipt['daemon_code'] = $json->daemon_code;
			$receipt['device_code'] = $json->device_code;
			 
			$this->updateOrderReceipt($receipt);
		}
	}
	 
	
	
	public function getToken(){
		$shopid = $this->config->get('ecomkassa_shopid');
		$password = $this->config->get('ecomkassa_password');
		$login = $this->config->get('ecomkassa_login');
		$url =trim(  $this->config->get('ecomkassa_url'), '/'). '/getToken';  
		
		
		$postdata = [
			'login' => $login,
			'pass' => $password
			
		];
 
		$response = $this->curlFunction( $url,  $postdata, true);
		 
		$json = json_decode($response);
		if($json->code>=2){ //error

			return false;
		}else{
			$this->token = $json->token;
			
			return $json->token;
		}
		
	}
	
	public function sell($order_info, $authToken){
		$shop_id = $this->config->get('ecomkassa_shopid');
		 
		$url =trim(  $this->config->get('ecomkassa_url'), '/').'/'.$shop_id. '/sell?tokenid='.$authToken;  
		$order_products = $this->getOrderProducts($order_info['order_id']);
		$order_totals = $this->getOrderTotals($order_info['order_id']);
		 
			$request['external_id'] = $order_info['order_id'];
		
			
			if(empty($order_info['email'])){
				$order_info['email'] = $this->config->get('config_email');
			}
			
			$request['receipt']['client']['email'] =$order_info['email']; 
			$phone =  str_replace(' ', '', $order_info['telephone']);
			$phone =  str_replace('+', '', $phone);
			$phone =  str_replace('(', '', $phone);
			$phone =  str_replace(')', '', $phone);
			
			$request['receipt']['client']['phone'] = $phone ; 
			
			$request['receipt']['company']['sno'] = $this->config->get('ecomkassa_sno');      
			$request['receipt']['company']['email'] = $this->config->get('config_email');      
			$request['receipt']['company']['inn'] = $this->config->get('ecomkassa_inn');      
			$request['receipt']['company']['payment_address'] = $order_info['store_url'];      
			$request['receipt']['vat']['type'] = $this->config->get('ecomkassa_vat') ;   
			 
			foreach($order_products as $order_product){
 
				$item['name'] = $order_product['name'];
				$item['price'] = round($order_product['price'],2);
				$item['quantity'] =(float) $order_product['quantity'];
				$item['sum']= round($order_product['total'],2);
				$item['payment_method']= 'full_prepayment';
				$item['payment_object']= 'commodity';
				$item['tax'] = $this->config->get('ecomkassa_vat');      
				$tax = $this->get_vat(round($order_product['price'],2),$this->config->get('ecomkassa_vat') );      
				if($tax){
					$item['tax_sum'] = $tax;
				}
				$request['receipt']['items'][] = $item;
			}
			foreach($order_totals as $order_total){
				if( $order_total['code'] == 'shipping' || $order_total['code'] == 'coupon'  ||  $order_total['code'] == 'voucher'){
					$item['name'] = $order_total['title'];
					$item['price'] = round($order_total['value'],2);
					$item['quantity'] =(float) 1;
					$item['sum']= round($order_total['value'],2);
					$item['payment_method']= 'full_prepayment';
					if( $order_total['code'] == 'shipping'){
						$item['payment_object']= 'service';
					}else{
						$item['payment_object']= 'payment';
					} 
					if($order_total['code'] == 'coupon' ||  $order_total['code'] == 'voucher'){
						$vat = 'none';
					}else{
						$vat = $this->config->get('ecomkassa_vat');  
					}
					
					$item['tax'] = $vat ;  
					$tax = $this->get_vat(round($order_total['value'],2),$vat );      
					if($tax){
						$item['tax_sum'] = $tax;
					}
	 
					$request['receipt']['items'][] = $item;

				}
			}

			$payment['sum']  =  round($order_info['total'],2);
			$payment['type'] = 1;
			$request['receipt']['payments'][] = $payment;   
 
			
			$request['receipt']['total']  =   round($order_info['total'],2);  
			
			$callback_url = new Url(HTTP_SERVER, $this->config->get('config_secure') ? HTTP_SERVER : HTTPS_SERVER);
			$callback_url =  $callback_url->link('module/ecomkassa/callback' );
			$request['service']['callback_url'] = $callback_url;
			$request['service']['inn'] =$this->config->get('ecomkassa_inn');   
			$request['service']['payment_address'] = $order_info['store_url'];
			$request['timestamp'] = date("d.m.Y H:i:s");  

			$response = $this->curlFunction( $url,  $request, true);
			$json = json_decode($response);
			$this->addOrderReceipt($order_info,$request,$json ,'sell' );
	}
	
	
	public function addOrderReceipt($order_info, $request, $json ,$type='sell'){
	
				$status = $json->status;
				$uuid = $json->uuid;
				$timestamp = $json->timestamp;
		
				$this->db->query("INSERT INTO " . DB_PREFIX . "ecomkassa SET 
						date_created = NOW(),
						date_modified = NOW(),
						order_id =  '" . (int)$order_info['order_id']  . "',
						customer_info =  '" . $this->db->escape($order_info['email']) . ';' . $this->db->escape($order_info['telephone']). "',
						status = '" . $this->db->escape($status) . "',
						store_id = '" . (int)$this->config->get('config_store_id') . "',
						total = '" . $this->db->escape($order_info['total']). "',
						uuid = '" . $this->db->escape($uuid). "',
						timestamp = '" . $this->db->escape($timestamp). "',
						type = '" . $this->db->escape($type). "',
						request = '" . $this->db->escape(serialize($request)). "',
						sno = '" .$this->config->get('ecomkassa_sno'). "' 
						");
	}
	public function updateOrderReceipt($receipt){
	
				 
				$this->db->query("UPDATE " . DB_PREFIX . "ecomkassa SET 
					 
						date_modified = NOW(),
					 
						error = '" . $this->db->escape($receipt['error']). "',
						status = '" . $this->db->escape($receipt['status']). "',
						fn_number = '" . $this->db->escape($receipt['fn_number']). "',
						shift_number = '" . $this->db->escape($receipt['shift_number']). "',
						receipt_datetime = '" . $this->db->escape($receipt['receipt_datetime']). "',
						fiscal_receipt_number = '" . $this->db->escape($receipt['fiscal_receipt_number']). "',
						fiscal_document_number = '" . $this->db->escape($receipt['fiscal_document_number']). "',
						ecr_registration_number = '" . $this->db->escape($receipt['ecr_registration_number']). "',
						fiscal_document_attribute = '" . $this->db->escape($receipt['fiscal_document_attribute']). "',
						fns_site = '" . $this->db->escape($receipt['fns_site']). "',
						timestamp = '" . $this->db->escape($receipt['timestamp']). "',
						group_code = '" . $this->db->escape($receipt['group_code']). "',
						daemon_code = '" . $this->db->escape($receipt['daemon_code']). "',
						device_code = '" . $this->db->escape($receipt['device_code']). "' 
						WHERE id = '" .  (int)$receipt['id']. "' ");
 
	}
	public function get_vat($price, $tax){
		if($tax=='none'){
			return false;
		}
		if($tax=='vat0'){
			return 0;
		}
		if($tax=='vat10'){
			
			return round($price *0.1,2);
		}
		if($tax=='vat18'){
			return round($price *0.18,2);
		}
		
		if($tax=='vat110'){ //has nds 10
			return round($price *(18/110),2);
		}
		
		if($tax=='vat118'){ //has nds 18
			return round($price *(18/118),2);
		}
		return false;
	}
	public function getOrderReceipt($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ecomkassa WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}
	public function loadReceipt($uuid) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ecomkassa WHERE uuid = '" . $this->db->escape($uuid) . "'");

		return $query->row;
	}
	
	
	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}
	
	public function getWaiting() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ecomkassa WHERE status = 'wait' AND date_created < (NOW() - INTERVAL 300 SECOND)");
		return $query->rows;
	}
	
	private function curlFunction( $url,  $data, $post , $authToken = false) {
				
	 
				
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);	// возвращает веб-страницу
		curl_setopt($ch, CURLOPT_HEADER, 0);			// не возвращает заголовки
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	// переходит по редиректам
		curl_setopt($ch, CURLOPT_ENCODING, "");			// обрабатывает все кодировки
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120); 	// таймаут соединения
		curl_setopt($ch, CURLOPT_TIMEOUT, 120);			// таймаут ответа
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);		// останавливаться после 10-ого редиректа
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	
		
		if($post){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		
			if($authToken){
				curl_setopt($ch, CURLOPT_HTTPHEADER , array(
				'Authorization: '.$authToken,
				'Content-Type: application/json'
				));
			}else{
				curl_setopt($ch, CURLOPT_HTTPHEADER , array(
				'Content-Type: application/json'
				));
			}
			
		
		}

		$content = curl_exec( $ch );
		curl_close( $ch );
		
		return $content;
	} 
	
	
	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");

		return $query->rows;
	}
}
?>
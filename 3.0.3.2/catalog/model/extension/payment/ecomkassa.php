<?php 
class ModelExtensionPaymentEcomkassa extends Model {

	public $token = '';
  	public function checkTransaction($ecom_data ) {

		$status = $this->config->get('module_ecomkassa_status');
		if($status !=1){

            return;
		}

        file_put_contents(DIR_LOGS.'ecomkassa.log', 'checkTransaction call'.PHP_EOL , FILE_APPEND);

		$order_id = $ecom_data['order_info']['order_id'];
		$receipt = $this->getOrderReceipt($order_id);
		if(!empty($receipt)){
			return;
		}

		
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($order_id); 

        $order_payment_method = $order_info['payment_code']; 
        $order_status_id = $order_info['order_status_id'];



        $ecomkassa_payment = $this->config->get('module_ecomkassa_payment');
		if(!isset($ecomkassa_payment[$order_payment_method])){
			return;
		}

        //LOGS
        file_put_contents(DIR_LOGS.'ecomkassa.log', 'new order_status_id '.$order_status_id.PHP_EOL , FILE_APPEND);
        file_put_contents(DIR_LOGS.'ecomkassa.log', 'order_payment_method ' .$order_payment_method.PHP_EOL , FILE_APPEND);
        file_put_contents(DIR_LOGS.'ecomkassa.log', 'ecomkassa method setting '.$ecomkassa_payment[$order_payment_method].PHP_EOL , FILE_APPEND);
        file_put_contents(DIR_LOGS.'ecomkassa.log', 'ecomkassa settings '.print_r($ecomkassa_payment, true).PHP_EOL , FILE_APPEND);

       

        if($ecomkassa_payment[$order_payment_method] != $order_status_id ){
			return;
		}

		$shopid = $this->config->get('module_ecomkassa_shopid');
		$password = $this->config->get('module_ecomkassa_password');
		$login = $this->config->get('module_ecomkassa_login');
		$url = $this->config->get('module_ecomkassa_url');
		
		
		$authToken = $this->getToken();
		$this->sell($order_info, $authToken);
		
  	}
	
	public function getUpdate($receipt){	
	 
		$this->getToken();
 
		$shopid = $this->config->get('module_ecomkassa_shopid');
		$password = $this->config->get('module_ecomkassa_password');
		$login = $this->config->get('module_ecomkassa_login');
		$url =trim(  $this->config->get('module_ecomkassa_url'), '/').'/'.$shopid. '/report/'.$receipt['uuid'].'?tokenid='.$this->token;  
 
		$response = $this->curlFunction( $url,  array(), false);
		file_put_contents(DIR_LOGS.'ecomkassa.log', 'response'.PHP_EOL. $response.PHP_EOL.PHP_EOL, FILE_APPEND);
		$json = json_decode($response);
		if(!$json){
		 
			return;
		}
		
		
		if(empty($json->status )){
			return;
		}
		
		if($json->status == 'done' || $json->status == 'fail'){
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
		$shopid = $this->config->get('module_ecomkassa_shopid');
		$password = $this->config->get('module_ecomkassa_password');
		$login = $this->config->get('module_ecomkassa_login');
		$url =trim(  $this->config->get('module_ecomkassa_url'), '/'). '/getToken';  
		
		
		$postdata = [
			'login' => $login,
			'pass' => $password
			
		];

        $postdata_log = [
            'login' => $login,
            'pass' => "***"

        ];
		file_put_contents(DIR_LOGS.'ecomkassa.log', 'request'.PHP_EOL. print_r($postdata_log, true).PHP_EOL.PHP_EOL, FILE_APPEND);
		$response = $this->curlFunction( $url,  $postdata, true);
		file_put_contents(DIR_LOGS.'ecomkassa.log', 'response'.PHP_EOL. $response.PHP_EOL.PHP_EOL, FILE_APPEND);
		$json = json_decode($response);
		if($json->code>=2){ //error

			return false;
		}else{
			$this->token = $json->token;
			
			return $json->token;
		}
		
	}
	
	public function sell($order_info, $authToken){
		$shop_id = $this->config->get('module_ecomkassa_shopid');
		 
		$url =trim(  $this->config->get('module_ecomkassa_url'), '/').'/'.$shop_id. '/sell?tokenid='.$authToken;  
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
			$phone = '';
			//$request['receipt']['client']['phone'] = '+'. $phone ;
			
			$request['receipt']['company']['sno'] = $this->config->get('module_ecomkassa_sno');      
			$request['receipt']['company']['email'] = $this->config->get('config_email');      
			$request['receipt']['company']['inn'] = $this->config->get('module_ecomkassa_inn');      
			$request['receipt']['company']['payment_address'] = $order_info['store_url'];      
			$request['receipt']['vats'][0]['type'] = $this->config->get('module_ecomkassa_vat') ;      
			$request['receipt']['vats'][0]['sum'] = $this->get_vat(round($order_info['total'],2),$this->config->get('module_ecomkassa_vat') );        
			 
			 $coupons = 0; 
			 $discount = 0;
			 $spare = 0;
			
			foreach($order_totals as $order_total){
				if(  $order_total['code'] == 'coupon'  ){
					$coupons +=  abs(round($order_total['value'],2));
				}
			}
			if($coupons > 0){
				$discount = $coupons /  count($order_products);
				$discount =  round($discount,2) ;
				$spare = $coupons  - ($discount *  count($order_products));
				$spare = round($spare,2) ;
				foreach($order_products as $order_product){
					if($order_product['total'] < $discount ){
						$spare += $discount  - $order_product['total'] + 0.01 ;
					}
				}
			}			
			 
			foreach($order_products as $order_product){
 
				$item['name'] = $order_product['name'];
				$item['price'] = round($order_product['price'],2);
				$item['quantity'] =(float) $order_product['quantity'];
				$item['payment_object']= 'commodity';
				$item['sum']= round($order_product['total'],2);
				
				$item['sum'] = $item['sum'] - $discount;
				if($order_product['total'] < $discount ){
					$item['sum'] = 0.01;
				}
				
				
				$item['payment_method']= 'full_payment'; //todo
				$item['tax'] = $this->config->get('module_ecomkassa_vat');      
				$item['vat']['type'] = $this->config->get('module_ecomkassa_vat') ;    
				$tax = $this->get_vat(round($order_product['price'],2),$this->config->get('module_ecomkassa_vat') );      
				if($tax){
					$item['tax_sum'] = $tax;
				}
				
				if($item['sum']  > $spare + 0.01  && $spare  != 0){
					$item['sum'] = $item['sum']  - $spare ;
					$spare = 0;
				}
				
				$request['receipt']['items'][] = $item;
			}	
			 
			foreach($order_totals as $order_total){
				if( $order_total['code'] == 'shipping' ||   $order_total['code'] == 'voucher'){ //$order_total['code'] == 'coupon'  ||
					$item['name'] = $order_total['title'];
					$item['price'] = round($order_total['value'],2);
					$item['quantity'] =(float) 1;
					$item['sum']= round($order_total['value'],2);
					$item['payment_method']= 'full_payment'; //todo
					if( $order_total['code'] == 'shipping'){
						$item['payment_object']= 'service';
					}else{
						$item['payment_object']= 'payment';
					} 
					if($order_total['code'] == 'coupon' ||  $order_total['code'] == 'voucher'){
						$vat = 'none';
					}else{
						$vat = $this->config->get('module_ecomkassa_vat');  
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
			$callback_url =  $callback_url->link('extension/module/ecomkassa/callback' );
			$request['service']['callback_url'] = $callback_url;
			$request['service']['inn'] =$this->config->get('module_ecomkassa_inn');   
			$request['service']['payment_address'] = $order_info['store_url'];
			$request['timestamp'] = date("d.m.Y H:i:s");  
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'request'.PHP_EOL. print_r($request, true).PHP_EOL.PHP_EOL, FILE_APPEND);
			$response = $this->curlFunction( $url,  $request, true, $authToken);
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'response'.PHP_EOL. $response.PHP_EOL.PHP_EOL, FILE_APPEND);
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
						sno = '" .$this->config->get('module_ecomkassa_sno'). "' 
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
	
	public function get_vat($price, $tax){
		if($tax=='none'){
			return 0;
		}
		if($tax=='vat0'){
			return 0;
		}
		if($tax=='vat10'){
			
			return round($price *0.1,2);
		}
		if($tax=='vat18'){
			return round($price * 0.18,2);
		}
		if($tax=='vat20'){
			return round($price * 0.20,2);
		}
		
		if($tax=='vat110'){ //has nds 10
			return round($price *(18/110),2);
		}
		
		if($tax=='vat118'){ //has nds 18
			return round($price *(18/118),2);
		}
		return false;
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
				'Token: '.$authToken,
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
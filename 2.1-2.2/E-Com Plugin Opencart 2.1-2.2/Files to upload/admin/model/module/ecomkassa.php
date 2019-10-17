<?php 
class ModelModuleEcomkassa extends Model {

	public $token = '';
	 public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "ecomkassa` (
			`id` INT(11) NOT NULL AUTO_INCREMENT,
			`date_created` DATETIME NULL DEFAULT NULL, 
			`date_modified` DATETIME NULL DEFAULT NULL, 
			`order_id` INT(11) NULL DEFAULT 0, 
			`customer_info` TEXT NULL DEFAULT NULL,
			`status` VARCHAR(255) NULL DEFAULT NULL,
			`store_id` TINYINT NOT NULL DEFAULT 0,  
			`uuid` VARCHAR(255) NULL DEFAULT NULL,
			`error` VARCHAR(255) NULL DEFAULT NULL,
			`total` double(8,2) DEFAULT NULL,
			`fn_number` VARCHAR(255) NULL DEFAULT NULL,
			`shift_number` VARCHAR(255) NULL DEFAULT NULL,
			`receipt_datetime` VARCHAR(255) NULL DEFAULT NULL,
			`fiscal_receipt_number` VARCHAR(255) NULL DEFAULT NULL,
			`fiscal_document_number` VARCHAR(255) NULL DEFAULT NULL,
			`ecr_registration_number` VARCHAR(255) NULL DEFAULT NULL,
			`fiscal_document_attribute` VARCHAR(255) NULL DEFAULT NULL,
			`fns_site` VARCHAR(255) NULL DEFAULT NULL,
			`timestamp` VARCHAR(255) NULL DEFAULT NULL,
			`group_code` VARCHAR(255) NULL DEFAULT NULL,
			`daemon_code` VARCHAR(255) NULL DEFAULT NULL,
			`device_code` VARCHAR(255) NULL DEFAULT NULL,
			`request` TEXT NULL DEFAULT NULL,
			`sno` VARCHAR(255) NULL DEFAULT NULL,
			`type` VARCHAR(255) NULL DEFAULT NULL,
			 PRIMARY KEY (`id`))");
	 
			 
			 
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET status=1 WHERE `name` LIKE'%Ecomkassa%'");
		$modifications = $this->load->controller('extension/modification/refresh');
	}
	
	public function uninstall() {
	 	$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "ecomkassa`");
	
		$this->db->query("UPDATE `" . DB_PREFIX . "modification` SET status=0 WHERE `name` LIKE'%Ecomkassa%'");
		$modifications = $this->load->controller('extension/modification/refresh');
	} 
	
	
	function getExtensions($type) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "extension WHERE `type` = '" . $this->db->escape($type) . "'");
		return $query->rows;
	}
	
 	public function getOrderReceipt($order_id, $type=false) {
		if($type){
			$where = " AND type = '" . $this->db->escape($type) . "'";
		
		}else{
			$where = '';
		}
	
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ecomkassa WHERE order_id = '".(int)$order_id."' $where ");
		return $query->row;
	}
	
	public function loadReceipt($uuid) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "ecomkassa WHERE uuid = '" . $this->db->escape($uuid) . "'");
		return $query->row;
	}
	
		
	public function addOrderReceipt($order_info,$request, $json, $type='sell'){
	
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
						uuid = '" . $this->db->escape($uuid). "',
						timestamp = '" . $this->db->escape($timestamp). "',
						total = '" . $this->db->escape($order_info['total']). "',
						type = '" . $this->db->escape($type ). "',
						request = '" . $this->db->escape(serialize($request)). "',
						sno = '" .$this->db->escape($this->config->get('ecomkassa_sno')). "' 
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
				$item['sum']= round($order_product['total'],2);
				
				$item['sum'] = $item['sum'] - $discount;
				if($order_product['total'] < $discount ){
					$item['sum'] = 0.01;
				}
				
				
				$item['payment_method']= 'full_prepayment';
				$item['payment_object']= 'commodity';
				$item['tax'] = $this->config->get('ecomkassa_vat');      
				$tax = $this->get_vat(round($order_product['price'],2),$this->config->get('ecomkassa_vat') );      
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
				if( $order_total['code'] == 'shipping'  ||  $order_total['code'] == 'voucher'){
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
			
			$debug = true;
			if($debug){
				file_put_contents (DIR_LOGS."ecom_request_sell.log",   json_encode($request) );
				file_put_contents (DIR_LOGS."ecom_response_sell.log",   $response );
			}
			
			
			$json = json_decode($response);
			$this->addOrderReceipt($order_info,$request, $json, 'sell' );
	 
 
			return $json;
		 
	}
	
	public function refund($order_info,$receipt, $authToken){
		$shop_id = $this->config->get('ecomkassa_shopid');
		 
		$url =trim(  $this->config->get('ecomkassa_url'), '/').'/'.$shop_id. '/sell_refund?tokenid='.$authToken;  
 
		$request =  unserialize($receipt['request']);
		
			 
		$response = $this->curlFunction( $url,  $request, true);
	 
		 
		$json = json_decode($response);
		 
		$this->addOrderReceipt($order_info,$request, $json,'refund' );
		
		return $json;
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
	/*
	public function correction($order_info,$receipt, $authToken){
		$shop_id = $this->config->get('ecomkassa_shopid');
		 
		$url =trim(  $this->config->get('ecomkassa_url'), '/').'/'.$shop_id. '/sell_correction?tokenid='.$authToken;  
		$order_products = $this->getOrderProducts($order_info['order_id']);
		 
			$request['external_id'] = $order_info['order_id'];
		
			
			if(empty($order_info['email'])){
				$order_info['email'] = $this->config->get('config_email');
			}
			
			//$request['correction']['attributes']['email'] =$order_info['email']; 
			//$phone =  str_replace(' ', '', $order_info['telephone']);
			
			//$request['correction']['attributes']['phone'] = $phone ; 
			if(!empty($this->config->get('ecomkassa_sno'))){
			$request['correction']['attributes']['sno'] = $this->config->get('ecomkassa_sno');      
			 }
			 
			 $request['correction']['attributes']['tax']  = $this->config->get('ecomkassa_vat');      

			$payment['sum']  =  (float)round($order_info['total']);
			$payment['type'] = 1;
			$request['correction']['payments'][] = $payment;   

			//debug
			$order_info['total'] = 0.01;
			//debug
			
			//$request['receipt']['total']  =   round($order_info['total'],2);  
			
			$callback_url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTP_CATALOG : HTTPS_CATALOG);
			$callback_url =  $callback_url->link('module/ecomkassa/callback' );
			$request['service']['callback_url'] = $callback_url;
			 $request['service']['inn'] =$this->config->get('ecomkassa_inn');   
			 $request['service']['payment_address'] = $order_info['store_url'];
			$request['timestamp'] = date("d.m.Y H:i:s");  
			echo '<pre>';
			echo '<h3>url</h3>';
			
			 var_dump($url);
			 echo '<h3>request</h3>';
			 var_dump(json_encode($request));
			$response = $this->curlFunction( $url,  $request, true);
		 echo '<h3>response</h3>';
		 var_dump($response);
		 
			$json = json_decode($response);
		 //"{"uuid":"196","timestamp":"01.12.2017 17:08:55","status":"wait","error":null}"
		 $this->addOrderReceipt($order_info,$json );
		 echo '</pre>';
		 
		 //todo add to check queue
		 
		 
	}*/
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
		
	public function getOrderProducts($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");

		return $query->rows;
	}	
		
		
	public function getCheck($login, $pass, $url){
		$url = trim( $url, '/'). '/getToken';
		$postdata = [
			'login' => $login,
			'pass' => $pass
			
		];
		$response = $this->curlFunction( $url,  $postdata, true);
		return $response;
	}
	
	private function curlFunction($url,  $data, $post , $authToken = false) {
				
		if(!$post){
			$url = $url .'?'. http_build_query($data);
		}		
				
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
		
		if($post){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
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
<?php 
class ModelExtensionPaymentEcomkassa extends Model {

	public $token = '';
  	public function checkTransaction($ecom_data ) {

		$status = $this->config->get('ecomkassa_status');
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

		if(empty($order_status_id) ){
			return;
		}
		
	
		$ecomkassa_payment = $this->config->get('ecomkassa_payment');
		if(!isset($ecomkassa_payment[$order_payment_method])){
			return;
		}
		
		
		 //LOGS
        file_put_contents(DIR_LOGS.'ecomkassa.log', 'new order_status_id '.$order_status_id.PHP_EOL , FILE_APPEND);
        file_put_contents(DIR_LOGS.'ecomkassa.log', 'order_payment_method ' .$order_payment_method.PHP_EOL , FILE_APPEND);
        file_put_contents(DIR_LOGS.'ecomkassa.log', 'ecomkassa method setting '.$ecomkassa_payment[$order_payment_method].PHP_EOL , FILE_APPEND);
        file_put_contents(DIR_LOGS.'ecomkassa.log', 'ecomkassa settings '.print_r($ecomkassa_payment, true).PHP_EOL , FILE_APPEND);

		if(  $ecomkassa_payment[$order_payment_method] == -1 ) {
		 
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
		$url =trim(  $this->config->get('ecomkassa_url'), '/').'/'.$shopid. '/report/'.$receipt['uuid'].'?token='.$this->token;  
 
		$response = $this->curlFunction( $url,  array(), false);
		file_put_contents(DIR_LOGS.'ecomkassa.log', 'getUpdate response'.PHP_EOL. $response.PHP_EOL.PHP_EOL, FILE_APPEND);
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
		$shopid = $this->config->get('ecomkassa_shopid');
		$password = $this->config->get('ecomkassa_password');
		$login = $this->config->get('ecomkassa_login');
		$url =trim(  $this->config->get('ecomkassa_url'), '/'). '/getToken';  
		
		
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
		$shop_id = $this->config->get('ecomkassa_shopid');
		file_put_contents(DIR_LOGS.'ecomkassa.log', 'line '.__LINE__ . ' sell '   .PHP_EOL.PHP_EOL, FILE_APPEND);
		$url =trim(  $this->config->get('ecomkassa_url'), '/').'/'.$shop_id. '/sell?token='.$authToken;  
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
			//$request['receipt']['vat']['type'] = $this->config->get('ecomkassa_vat') ;    
			 
			 $coupons = 0;
			 $sum = 0;
			 $shipping = 0;
			// $discount = 0;
			// $spare = 0;
			
			$total = round($order_info['total'],2);
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'line '.__LINE__ . ' total ' . $total   .PHP_EOL.PHP_EOL, FILE_APPEND);
			foreach($order_products as $order_product){
			 
				$sum +=  abs(round($order_product['total'],2));
				 
			}
			foreach($order_totals as $order_total){
				if( $order_total['code'] == 'shipping'    ){
						$shipping +=  abs(round($order_total['value'],2));
				}
			}
			$coupons =  abs($total - $shipping - $sum) ;
			$coupons = round($coupons,2);
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'line '.__LINE__ . ' sum products ' . $sum   .PHP_EOL.PHP_EOL, FILE_APPEND); 	
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'line '.__LINE__ . ' sum shipping ' . $shipping   .PHP_EOL.PHP_EOL, FILE_APPEND); 	
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'line '.__LINE__ . ' coupons ' . $coupons   .PHP_EOL.PHP_EOL, FILE_APPEND); 	
			 
			foreach($order_products as $order_product){
 
				$item['name'] = $order_product['name'];
				$item['price'] = round($order_product['price'],2);
				$item['quantity'] =(float) $order_product['quantity'];
				$item['sum']= round($order_product['total'],2);
				
				//$item['sum'] = $item['sum'] - $discount;
				//if($order_product['total'] < $discount ){
				//	$item['sum'] = 0.01;
				//}
				//$item['sum'] =(float) $item['sum'];
				$item['payment_object']= 'commodity';
				$item['tax'] = $this->config->get('ecomkassa_vat');   
				
				$vat = $this->config->get('ecomkassa_vat');  
				$item['vat']['type'] = $vat ;  				
				$item['payment_method']= 'full_payment'; //todo
				$tax = $this->get_vat(round($order_product['price'],2),$this->config->get('ecomkassa_vat') );      
				if($tax){
					$item['tax_sum'] = $tax;
				}
				
				
				
				
				$request['receipt']['items'][] = $item;
			} 
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'line '.__LINE__ . ' coupons is ' . $coupons  .PHP_EOL.PHP_EOL, FILE_APPEND);
			if( $coupons  > 0 ){
				$request['receipt']['items'] = $this->calculate_discount($request['receipt']['items'], $coupons);
				
			}
			
			foreach($order_totals as $order_total){
				if( $order_total['code'] == 'shipping'    ){
					$item['name'] = $order_total['title'];
					$item['price'] = round($order_total['value'],2);
					$item['quantity'] =(float) 1;
					$item['sum']= round($order_total['value'],2);
					$item['sum'] =(float) $item['sum'];
					$item['payment_method']= 'full_payment';  //todo
					if( $order_total['code'] == 'shipping'){
						$item['payment_object']= 'service';
					}else{
						$item['payment_object']= 'payment';
					} 
					if($order_total['code'] == 'coupon' ){ //||  $order_total['code'] == 'voucher'
						$vat = 'none';
					}else{
						$vat = $this->config->get('ecomkassa_vat');  
					}
				 
					$item['vat']['type'] = $vat ;  
					$tax = $this->get_vat(round($order_total['value'],2),$vat );      
					if($tax){
						$item['tax_sum'] = $tax;
					}
	 
					$request['receipt']['items'][] = $item;

				}
			}

			$payment['sum']  =  round($order_info['total'],2);
			$payment['sum'] =(float) $payment['sum'];
			$payment['type'] = 1;
			$request['receipt']['payments'][] = $payment;   
 
			
			$request['receipt']['total']  =   round($order_info['total'],2);  
			$request['receipt']['total']  =(float)$request['receipt']['total'] ;
			$callback_url = new Url(HTTP_SERVER, $this->config->get('config_secure') ? HTTP_SERVER : HTTPS_SERVER);
			$callback_url =  $callback_url->link('extension/module/ecomkassa/callback' );
			$request['service']['callback_url'] = $callback_url;
			$request['service']['inn'] =$this->config->get('ecomkassa_inn');   
			$request['service']['payment_address'] = $order_info['store_url'];
			$request['timestamp'] = date("d.m.Y H:i:s");  
			
			
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'request'.PHP_EOL. print_r($request, true).PHP_EOL.PHP_EOL, FILE_APPEND);
			$response = $this->curlFunction( $url,  $request, true, $authToken);
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'response'.PHP_EOL. $response.PHP_EOL.PHP_EOL, FILE_APPEND);
			$json = json_decode($response);
			
			if ($json === null 	&& json_last_error() !== JSON_ERROR_NONE) {
			 
				file_put_contents(DIR_LOGS.'ecomkassa.log', 'incorrect json data'.PHP_EOL. $response.PHP_EOL.PHP_EOL, FILE_APPEND);
			}
			$this->addOrderReceipt($order_info,$request,$json ,'sell' );
	}
	
	
	public function calculate_discount($products, $coupons){
		
		file_put_contents(DIR_LOGS.'ecomkassa.log', 'calculate_discount ' . $coupons  .PHP_EOL. print_r($products, true) .PHP_EOL.PHP_EOL, FILE_APPEND);
		
		$discount = 0;
		$spare = 0;
		
		
		//first calc if coupons is integer
		if( $coupons*100 % 100 == 0 ){
			$data = $this->first_calc($products, $coupons);
			//$products = $data['products'];
			$spare = $data['spare'];
			
			if($spare == 0){
				return  $data['products'];
			}
		} 
		
		
		$spare = ($coupons*100) % count($products);
		$spare = $spare /100;
			
		
		$discount = ($coupons-$spare) /  count($products);
		
		
		//calculate discount per line
		$discount =  round($discount,2) ;
		
		//calculate spare
		//$spare = $coupons  - ($discount *  count($products));
		//$spare = round($spare,2) ;
 
		//try to substract discount and add spare 
		foreach($products as $i => $item){
			 
			
			if($item['sum'] < $discount ){
				$item['sum'] = 0.00;
				$spare += $discount  - $item['sum']  ;
			}else{
				$item['sum'] = $item['sum'] - $discount;
				
			}
				
			//check if item sum cannot divide in quantity	
			if( ($item['sum']*100) % $item['quantity']  != 0){
				$left = ($item['sum']*100) % $item['quantity'];
				$left = $left/100;
				$item['sum'] = $item['sum'] - $left;
				$spare += $left;
				echo 'Spare is ' .$spare . ' for item' .  $item['name']  . PHP_EOL;
			}
			
			$products[$i] = $item;
		}	
		
		echo 'Spare is ' . $spare. PHP_EOL;
		
		//second calc  //0.01
		if($spare > 0){
			$data = $this->second_calc($products, $spare);
			$products = $data['products'];
			$spare = $data['spare'];
		}
		
		//third calc
		if($spare > 0){
			$data = $this->third_calc($products, $spare);
			$products = $data['products'];
			$spare = $data['spare'];
		}
		//fourth calc
		if($spare > 0){
			$data = $this->fourth_calc($products, $spare);
			$products = $data['products'];
			$spare = $data['spare'];
		}
 
		return $products;
	}
	
	public function first_calc($products, $coupons){
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'first_calc ' . $coupons  .PHP_EOL. print_r($products, true) .PHP_EOL.PHP_EOL, FILE_APPEND);
		$spare = 0;
		$sum  =0 ;
		$item_discounts  =0 ;
		 foreach($products as $i => $item){
			 $sum += $item['sum'] ;
		 }
	 
		$arrayKeys = array_keys($products);
		// Fetch last array key
		$lastArrayKey = array_pop($arrayKeys);
		 
		foreach($products as $i => $item){
			 //if last 
			 if($i == $lastArrayKey ){
			    //  echo 'last  '.  PHP_EOL;
			    // echo '$coupons  '.  $coupons . ' $item_discounts '. $item_discounts. PHP_EOL;
				 $item_discount = $coupons -  $item_discounts;
			 }else{
			     // echo ' not last  '.  PHP_EOL;
			      
				$weight =  $item['sum'] / $sum;
				
				 //echo '$weight  '.  $weight . ' $sum '. $sum. PHP_EOL;
				
				$item_discount =  round($weight * $coupons);
				 //echo '$item_discount  '.  $item_discount .  PHP_EOL;
				$item_discounts += $item_discount;
				 //echo '$item_discounts  ' .  $item_discounts . PHP_EOL;
			 }
 
			$item['sum'] = $item['sum'] - $item_discount;

			
			//check if item sum cannot divide in quantity	
			if( ($item['sum']*100) % $item['quantity']  != 0){
				$left = ($item['sum']*100) % $item['quantity'];
				$left = $left/100;
				$item['sum'] = $item['sum'] - $left;
				$spare += $left;
				//echo 'Spare is ' .$spare . ' for item' .  $item['price']  . PHP_EOL;
			}
			
			$products[$i] = $item;
		}
		 
			file_put_contents(DIR_LOGS.'ecomkassa.log', 'first_calc spare ' . $spare  .PHP_EOL. print_r($products, true) .PHP_EOL.PHP_EOL, FILE_APPEND);
			
		$data['products'] = $products;
		$data['spare'] = $spare;
		
		return $data;
	}
	
	public function second_calc($products, $spare){
		file_put_contents(DIR_LOGS.'ecomkassa.log', 'second_calc ' . $coupons  .PHP_EOL. print_r($products, true) .PHP_EOL.PHP_EOL, FILE_APPEND);
		foreach($products as $i => $item){
			if( ($spare*100) % $item['quantity'] == 0 && $item['sum'] >= $spare){
				$products[$i]['sum'] = $item['sum'] - $spare;
				$spare = 0;
			}
		}
		$data['products'] = $products;
		$data['spare'] = $spare;
		
		return $data;
	}
	public function third_calc($products, $spare){
		file_put_contents(DIR_LOGS.'ecomkassa.log', 'third_calc ' . $coupons  .PHP_EOL. print_r($products, true) .PHP_EOL.PHP_EOL, FILE_APPEND);
		foreach($products as $i => $item){
		foreach($products as $k => $second_item){
			if( ($spare*100) % ($item['quantity']+$second_item['quantity']) == 0 && $item['sum'] >= $spare){
				$total_quantity = $item['quantity']+$second_item['quantity'];
				
				//i weight
				$spare1 = round($spare * $item['quantity']/$total_quantity ,2) ;
				$products[$i]['sum'] = $item['sum'] - $spare1 ;
				
				//k weight
				$spare2 = round($spare * $second_item['quantity']/$total_quantity ,2) ;
				$products[$k]['sum'] = $item['sum'] - $spare2 ;
				
				
				if($spare  >  $spare1  + $spare2){
					
					$spare = $spare - ($spare1  + $spare2);
				}else{
					$spare = 0;
					
				}
			}
		}
		}
		$data['products'] = $products;
		$data['spare'] = $spare;
		
		return $data;
	}
	public function fourth_calc($products, $spare){  //split position and remove discount
		file_put_contents(DIR_LOGS.'ecomkassa.log', 'fourth_calc ' . $coupons  .PHP_EOL. print_r($products, true) .PHP_EOL.PHP_EOL, FILE_APPEND);
		foreach($products as $i => $item){
			if(  $item['sum'] >= $spare){
				
				$price = $item['sum'] / $item['quantity'];
				$count_items = $spare / $price ;
				if($count_items  < 0){
					//echo 'take no full ';
					
				}else{
					// echo 'take ' . (int)$delta. '  full ';
					$count_items = (int)$count_items;
					$spare = $spare - ($price  * $count_items); 
					
					$zero_itm = $item;
					$zero_itm['quantity'] = $count_items;
					$zero_itm['sum'] = 0;
					$item['quantity'] = $item['quantity'] - $count_items;
					$products[] = $zero_itm;
				}
				
				if($spare > 0 ){
					$substr_itm = $item;
					$substr_itm['quantity'] = 1;
					$substr_itm['sum'] = $substr_itm['sum'] - $spare ;
					$item['quantity'] = $item['quantity'] - 1;
					$products[] = $substr_itm;
					$spare = 0;
					
				}

			}
		}
		$data['products'] = $products;
		$data['spare'] = $spare;
		
		return $data;
	}
	
	
	public function addOrderReceipt($order_info, $request, $json ,$type='sell'){
	
				$status = $json->status;
				$uuid = $json->uuid;
				$timestamp = $json->timestamp;
				
				$str = "INSERT INTO " . DB_PREFIX . "ecomkassa SET 
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
						";
				file_put_contents(DIR_LOGS.'ecomkassa.log', 'sql: '.PHP_EOL. $str.PHP_EOL.PHP_EOL, FILE_APPEND);
				$this->db->query($str );
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
		if($tax=='vat20'){
			return round($price *0.20,2);
		}
		
		if($tax=='vat110'){ //has nds 10
			return round($price *(18/110),2);
		}
		
		if($tax=='vat118'){ //has nds 18
			return round($price *(18/118),2);
		}
		return false;
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
		
		
		
		}
			if($authToken){
				curl_setopt($ch, CURLOPT_HTTPHEADER , array(
				'Token: '.$authToken,
				'Content-Type: application/json; charset=utf-8',
				'Accept: application/json'
				));
			}else{
				curl_setopt($ch, CURLOPT_HTTPHEADER , array(
				'Content-Type: application/json; charset=utf-8',
				'Accept: application/json'
				));
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
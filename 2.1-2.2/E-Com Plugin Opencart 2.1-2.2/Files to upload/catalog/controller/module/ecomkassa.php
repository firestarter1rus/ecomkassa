<?php
class ControllerModuleEcomkassa extends Controller {
	public function index() {
	 
	}
	
	public function callback() {
	
		$status = $this->config->get('ecomkassa_status');
		if($status !=1){
			return;
		}
		
	
		$this->load->model('payment/ecomkassa');
		
	 
	 
		
		$inputJSON = file_get_contents('php://input');
		$debug = true;
		if($debug){
			file_put_contents (DIR_LOGS."ecom_callback.log",   $inputJSON );
		}
	 
		$json = json_decode($inputJSON);
		if(!$json){
			return;
		}
 
		if(empty($json->status )){
			return;
		}
		
		if($json->status == 'done' || $json->status == 'fail'){
		
			$uuid =  $json->uuid;
			$receipt =  $this->model_payment_ecomkassa->loadReceipt($uuid );
			if(empty($receipt)){
				return;
			}
		
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
			 
			$this->model_payment_ecomkassa->updateOrderReceipt($receipt);
		}
	}
	
	public function cron() {
 		$status = $this->config->get('ecomkassa_status');
		if($status !=1){
			return;
		}
		
		$this->load->model('payment/ecomkassa');
		$receipts = $this->model_payment_ecomkassa->getWaiting();
		foreach($receipts as $receipt){
			echo $receipt['uuid'];
			$this->model_payment_ecomkassa->getUpdate($receipt);
		}
 
	}
 
	
}
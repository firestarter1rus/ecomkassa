<?php
class ControllerExtensionModuleEcomkassa extends Controller { 
	private $error = array();

	
	
	public function install() {
		$this->load->model('extension/module/ecomkassa');
		$this->load->model('setting/store');
		$this->model_extension_module_ecomkassa->install();
	}
	
	public function uninstall() {
		$this->load->model('setting/store');
        $this->load->model('extension/module/ecomkassa');
        $this->model_extension_module_ecomkassa->uninstall();
	}
 
	public function index() {
		$this->load->language('module/ecomkassa');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ecomkassa', $this->request->post);
			
			
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_crosses'] = $this->language->get('text_crosses');
		$data['text_crosses_descr'] = $this->language->get('text_crosses_descr');
		$data['text_cоunt'] = $this->language->get('text_cоunt');
		$data['text_get_cross'] = $this->language->get('text_get_cross');
		$data['btn_analyze'] = $this->language->get('btn_analyze');
		$data['text_analyze_descr'] = $this->language->get('text_analyze_descr');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_deselect'] = $this->language->get('text_deselect');

		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_inn'] = $this->language->get('entry_inn');
		
		$data['entry_url'] = $this->language->get('entry_url');
		$data['entry_login'] = $this->language->get('entry_login');
		$data['entry_password'] = $this->language->get('entry_password');
		$data['entry_shopid'] = $this->language->get('entry_shopid');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		
		
		
		$data['ecom_tax'] = $this->language->get('ecom_tax');
		$data['ecom_tax_none'] = $this->language->get('ecom_tax_none');
		$data['ecom_tax_osn'] = $this->language->get('ecom_tax_osn');
		$data['ecom_tax_usn_income'] = $this->language->get('ecom_tax_usn_income');
		$data['ecom_tax_usn_income_outcome'] = $this->language->get('ecom_tax_usn_income_outcome');
		$data['ecom_tax_envd'] = $this->language->get('ecom_tax_envd');
		$data['ecom_tax_esn'] = $this->language->get('ecom_tax_esn');
		$data['ecom_tax_patent'] = $this->language->get('ecom_tax_patent');
		$data['ecom_connection'] = $this->language->get('ecom_connection');
		$data['ecom_about'] = $this->language->get('ecom_about');
		$data['ecom_settings'] = $this->language->get('ecom_settings');
		$data['ecom_shop'] = $this->language->get('ecom_shop');
		$data['ecom_btn_check'] = $this->language->get('ecom_btn_check');
		$data['ecom_entry_check'] = $this->language->get('ecom_entry_check');
		$data['ecom_payment_systems'] = $this->language->get('ecom_payment_systems');
		$data['ecom_dont_use'] = $this->language->get('ecom_dont_use');
		$data['ecom_cron'] = $this->language->get('ecom_cron');
		$data['ecom_status_heading'] = $this->language->get('ecom_status_heading');
		$data['ecom_vat'] = $this->language->get('ecom_vat');
		$data['ecom_vat_none'] = $this->language->get('ecom_vat_none');
		$data['ecom_vat_vat0'] = $this->language->get('ecom_vat_vat0');
		$data['ecom_vat_vat10'] = $this->language->get('ecom_vat_vat10');
		$data['ecom_vat_vat18'] = $this->language->get('ecom_vat_vat18');
		$data['ecom_vat_vat20'] = $this->language->get('ecom_vat_vat20');
		$data['ecom_vat_vat110'] = $this->language->get('ecom_vat_vat110');
		$data['ecom_vat_vat118'] = $this->language->get('ecom_vat_vat118');
		
		
		
		$data['check_url'] = $this->url->link('extension/module/ecomkassa/check', 'token=' . $this->session->data['token'], 'SSL');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->error['url'])) {
			$data['error_url'] = $this->error['url'];
		} else {
			$data['error_url'] = '';
		}
		
		if (isset($this->error['inn'])) {
			$data['error_inn'] = $this->error['inn'];
		} else {
			$data['error_inn'] = '';
		}
		
		if (isset($this->error['login'])) {
			$data['error_login'] = $this->error['login'];
		} else {
			$data['error_login'] = '';
		}
		 
		
		if (isset($this->error['password'])) {
			$data['error_password'] = $this->error['password'];
		} else {
			$data['error_password'] = '';
		}
		
		
		if (isset($this->error['shopid'])) {
			$data['error_shopid'] = $this->error['shopid'];
		} else {
			$data['error_shopid'] = '';
		}
		
		$url = new Url(HTTP_CATALOG, $this->config->get('config_secure') ? HTTP_CATALOG : HTTPS_CATALOG);
		$data['cron'] =  $url->link('extension/module/ecomkassa/cron' );
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/ecomkassa', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = $this->url->link('extension/module/ecomkassa', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->post['url'])) {
			$data['url'] = $this->request->post['ecomkassa_url'];
		} else {
			$data['url'] = $this->config->get('ecomkassa_url');
		}
		
		if (isset($this->request->post['sno'])) {
			$data['sno'] = $this->request->post['ecomkassa_sno'];
		} else {
			$data['sno'] = $this->config->get('ecomkassa_sno');
		}
		if (isset($this->request->post['inn'])) {
			$data['inn'] = $this->request->post['ecomkassa_inn'];
		} else {
			$data['inn'] = $this->config->get('ecomkassa_inn');
		}
		if (isset($this->request->post['vat'])) {
			$data['vat'] = $this->request->post['ecomkassa_vat'];
		} else {
			$data['vat'] = $this->config->get('ecomkassa_vat');
		}
		
		
		if (isset($this->request->post['login'])) {
			$data['login'] = $this->request->post['ecomkassa_login'];
		} else {
			$data['login'] = $this->config->get('ecomkassa_login');
		}
		
		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['ecomkassa_password'];
		} else {
			$data['password'] = $this->config->get('ecomkassa_password');
		}
		
		if (isset($this->request->post['shopid'])) {
			$data['shopid'] = $this->request->post['ecomkassa_shopid'];
		} else {
			$data['shopid'] = $this->config->get('ecomkassa_shopid');
		}
		
		if (isset($this->request->post['payment'])) {
			$data['payment'] = $this->request->post['ecomkassa_payment'];
		} else {
			$data['payment'] = $this->config->get('ecomkassa_payment');
		}
		
		if (isset($this->request->post['ecomkassa_status'])) {
			$data['status'] = $this->request->post['ecomkassa_status'];
		} else {
			$data['status'] = $this->config->get('ecomkassa_status');
		}
		
		
		$this->load->model('extension/module/ecomkassa');
 
	
		$this->load->model('extension/extension');
		$payment_systems = array();
		$sort_order = array();

		$results = $this->model_extension_module_ecomkassa->getExtensions('payment');
 
		$data['payment_systems'] = array();
		
		
			 

		// Compatibility code for old extension folders
		$files = glob(DIR_APPLICATION . 'controller/{extension/payment,payment}/*.php', GLOB_BRACE);

		
		
		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');
				$status = $this->config->get($extension . '_status');
				$this->load->language('extension/payment/' . $extension);

				$text_link = $this->language->get('text_' . $extension);

				 
				if($status == 1){
				$data['payment_systems'][] = array(
					'name'       => $this->language->get('heading_title'),
					'code'       => $extension 
				);
				
				}
			}
		}
		
		
		
/*
		$files = glob(DIR_APPLICATION . 'controller/extension/payment/*.php');

		if ($files) {
			foreach ($files as $file) {
				$extension = basename($file, '.php');

				$this->load->language('payment/' . $extension);
 
				if($this->config->get($extension . '_status')){
					$data['payment_systems'][] = array(
						'name'       => $this->language->get('heading_title'), 
						'code'       => $extension
 
					);
				}
			}
		}
		*/
		
			
		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses(); 
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/ecomkassa.tpl', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ecomkassa')) {
			$this->error['warning'] = $this->language->get('error_permission');
		
		}
		 
		if(empty($this->request->post['ecomkassa_inn'])){
			$this->error['inn'] ='ИНН необходим';
		}
		return !$this->error;
	}
	
	/*
	*	CONNECTION CHECK
	*/
	public function check(){
		$login = $this->request->post['login'];
		$pass = $this->request->post['pass'];
		$url = $this->request->post['url'];
		
		$this->load->model('extension/module/ecomkassa');
		$check = $this->model_extension_module_ecomkassa->getCheck($login, $pass, $url);
		
		echo $check;
	}
	
	
	/*
	*	SELL
	*/
	public function sell(){
		$order_id = $this->request->post['order_id'];
	
		$status = $this->config->get('ecomkassa_status');
		if($status !=1){
			$data['message'] = 'Модуль отключен';
			$data['success'] = false;
			echo json_encode($data);
			return;
		}
		$this->load->model('extension/module/ecomkassa');
		$receipt = $this->model_extension_module_ecomkassa->getOrderReceipt($order_id);
		if(!empty($receipt)){
			$data['message'] = 'Чек на продажу уже существует';
			$data['success'] = false;
			echo json_encode($data);
			return;
		}
		
		
		$this->load->model('sale/order');
		$order_info = $this->model_sale_order->getOrder($order_id); 

 
		$shopid = $this->config->get('ecomkassa_shopid');
		$password = $this->config->get('ecomkassa_password');
		$login = $this->config->get('ecomkassa_login');
		$url = $this->config->get('ecomkassa_url');
		
		
		$authToken = $this->model_extension_module_ecomkassa->getToken();
		if(!$authToken){
			$data['message'] = 'Не удалось получить токен';
			$data['success'] = false;
			echo json_encode($data);
			return ;
		}
		$result = $this->model_extension_module_ecomkassa->sell($order_info, $authToken);
		
	
		$data['result'] = $result;
		if( $result->error==null){
			$data['success'] = true;
			$data['message'] = 'Данные успешно отправлены, обновите страницу';
		}else{
			$data['success'] = false;
			$data['message'] = 'Произошла ошибка: '.$result->error;
		}
		echo json_encode($data);
		return ;
		
		
	 
 
	}
	
	
	/*
	*	SELL REFUND
	*/
	public function sellRefund(){
		$order_id = $this->request->post['order_id'];
	
		$status = $this->config->get('ecomkassa_status');
		if($status !=1){
			$data['message'] = 'Модуль отключен';
			$data['success'] = false;
			echo json_encode($data);
			return;
		}
		$this->load->model('extension/module/ecomkassa');
		$receipt = $this->model_extension_module_ecomkassa->getOrderReceipt($order_id, 'refund');
		if(!empty($receipt)){
			$data['message'] = 'Чек на возврат уже существует';
			$data['success'] = false;
			echo json_encode($data);
			return;
		}
		$receipt = $this->model_extension_module_ecomkassa->getOrderReceipt($order_id, 'sell');
		if(empty($receipt)){
			$data['message'] = 'Чек на продажу не существует';
			$data['success'] = false;
			echo json_encode($data);
			return;
		}
		
		$this->load->model('sale/order');
		$order_info = $this->model_sale_order->getOrder($order_id); 

 
		$shopid = $this->config->get('ecomkassa_shopid');
		$password = $this->config->get('ecomkassa_password');
		$login = $this->config->get('ecomkassa_login');
		$url = $this->config->get('ecomkassa_url');
		
		
		$authToken = $this->model_extension_module_ecomkassa->getToken();
		if(!$authToken){
			$data['message'] = 'Не удалось получить токен';
			$data['success'] = false;
			echo json_encode($data);
			return ;
		}
		$result = $this->model_extension_module_ecomkassa->refund($order_info,$receipt, $authToken);
		
		$data['result'] = $result;
		if( $result->error==null){
			$data['success'] = true;
			$data['message'] = 'Данные успешно отправлены, обновите страницу';
		}else{
			$data['success'] = false;
			$data['message'] = 'Произошла ошибка: '.$result->error;
		}
		
		echo json_encode($data);
		return ;
		
	}
	
	
	/*
	*	SELL CORRECTION
	*/
	public function sellCorrection(){
		
		return;
		$order_id = $this->request->post['order_id'];
	
		$status = $this->config->get('ecomkassa_status');
		if($status !=1){
			return;
		}
		$this->load->model('extension/module/ecomkassa');
		$receipt = $this->model_extension_module_ecomkassa->getOrderReceipt($order_id);
		if(empty($receipt)){
			return;
		}
		
		
		$this->load->model('sale/order');
		$order_info = $this->model_sale_order->getOrder($order_id); 

 
		$shopid = $this->config->get('ecomkassa_shopid');
		$password = $this->config->get('ecomkassa_password');
		$login = $this->config->get('ecomkassa_login');
		$url = $this->config->get('ecomkassa_url');
		
		
		$authToken = $this->model_extension_module_ecomkassa->getToken();
		if(!$authToken){
			return false;
		}
		$result = $this->model_extension_module_ecomkassa->correction($order_info,$receipt, $authToken);
		echo $result;
	}
	
	
	
	
	
}
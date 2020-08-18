<?php
/*
Plugin Name: WooCommerce - E-COM kassa
Description: Фискализация платежей с помощью сервиса E-COM kassa Касса для плагина WooCommerce
Plugin URI: https://ecomkassa.ru/
Author: Империя Сайтов
Version: 1.0.0
Author URI: http://imperiaweb.ru/
*/

use Ecom\KassaSdk\Client;
use Ecom\KassaSdk\QueueManager;
use Ecom\KassaSdk\Check;
use Ecom\KassaSdk\Payment;
use Ecom\KassaSdk\Position;
use Ecom\KassaSdk\Vat;
use Ecom\KassaSdk\Exception\ClientException;
use Ecom\KassaSdk\Exception\ClientExceptionError;
use Ecom\KassaSdk\Exception\ClientExceptionErrorIncomingMissingToken;
use Ecom\KassaSdk\Exception\ClientExceptionErrorIncomingValidationException;
use Ecom\KassaSdk\Exception\ClientExceptionErrorAuthWrongUserOrPassword;
use Ecom\KassaSdk\Exception\SdkException;

final class EcomKassa {

	public $version = '1.0.0';

	const DISCOUNT_NOT_AVAILABLE = 0;

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->define( 'ECOMKASSA_ABSPATH', plugin_dir_path( __FILE__ ) );
		$this->define( 'ECOMKASSA_ABSPATH_VIEWS', plugin_dir_path( __FILE__ ) . 'includes/views/' );
		$this->define( 'ECOMKASSA_BASENAME', plugin_basename( __FILE__ ) );

		$this->includes();
		$this->hooks();
		$this->wp_hooks();
		$this->wp_endpoints();
		$this->load_options();
		$this->init();
	}

	public function wp_hooks() {
		register_activation_hook( __FILE__, array( 'EcomKassa_Install', 'activation' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'order_status_changed' ));
		add_action( 'add_meta_boxes', array( $this, 'order_receipts_meta_boxes' ) );

		add_action( 'woocommerce_order_actions', array( $this, 'add_order_check_meta_box_action' ) );
		add_action( 'woocommerce_order_action_wc_order_check_action_sell', array( $this, 'wc_process_order_check_action_sell' ) );
		add_action( 'woocommerce_order_action_wc_order_check_action_sell_refund', array( $this, 'wc_process_order_check_action_sell_refund' ) );
		add_action( 'woocommerce_order_action_wc_order_check_action_buy', array( $this, 'wc_process_order_check_action_buy' ) );
		add_action( 'woocommerce_order_action_wc_order_check_action_buy_refund', array( $this, 'wc_process_order_check_action_buy_refund' ) );
	}

	public function wp_endpoints() {
		add_action( 'parse_request', array( $this, 'handle_requests' ), 0 );
	}

	public function hooks() {
		add_action( 'ecom_kassa_report_create', array( $this, 'report_create' ), 10, 4 );
		add_action( 'ecom_kassa_report_update', array( $this, 'report_update' ), 10, 3 );
	}

	public function includes() {
		require_once( ECOMKASSA_ABSPATH . 'includes/class-ecomkassa-install.php' );
		require_once( ECOMKASSA_ABSPATH . 'includes/class-ecomkassa-report.php' );
		require_once( ECOMKASSA_ABSPATH . 'includes/class-ecomkassa-check.php' );
		require_once( ECOMKASSA_ABSPATH . 'includes/libs/ecom-kassa-php-sdk/autoload.php' );

		if ( is_admin() ) {
			require_once( ECOMKASSA_ABSPATH . 'includes/class-ecomkassa-admin.php' );
			add_action( 'init', array( 'EcomKassa_Admin', 'init' ) );
		}
	}

	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	public function load_options() {
		$this->server_url = get_option( 'ecomkassa_server_url' );
		$this->shop_id    = get_option( 'ecomkassa_shop_id' );
		$this->login      = get_option( 'ecomkassa_login' );
		$this->password   = get_option( 'ecomkassa_password' );
	}

	public function init() {
		do_action( 'before_ecomkassa_init' );
		$this->client = new Client( $this->shop_id, $this->login, $this->password );
		$this->client->setHost( $this->server_url );
		$this->queueManager = new QueueManager( $this->client );
		$this->report       = new EcomKassa_Report();
		$this->check        = new EcomKassa_Check();
		do_action( 'ecomkassa_init' );
	}

	public function taxSystems() {
		return array(
			Check::TS_COMMON            => 'ОСН',
			Check::TS_SIMPLIFIED_IN     => 'УСН доход',
			Check::TS_SIMPLIFIED_IN_OUT => 'УСН доход - расход',
			Check::TS_UTOII             => 'ЕНВД',
			Check::TS_UST               => 'ЕСН',
			Check::TS_PATENT            => 'Патент'
		);
	}
	
	public function paymentMethods() {
	    return array(
	        Position::PM_FULL_PREPAYMENT    => 'Предоплата 100%',
	        Position::PM_PREPAYMENT         => 'Частичная предоплата',
	        Position::PM_ADVANCE            => 'Аванс',
	        Position::PM_FULL_PAYMENT       => 'Полный расчет',
	        Position::PM_PARTIAL_PAYMENT    => 'Частичный расчет',
	        Position::PM_CREDIT             => 'Передача в кредит',
	        Position::PM_CREDIT_PAYMENT     => 'Оплата кредита'
	        );
	}

	public function checkPaymentMethod( $method_id ) {
		$available_payment_methods = get_option( 'ecomkassa_fiscalize_on_available_payment_gateways' );
		if ( ! in_array( $method_id, $available_payment_methods ) ) {
			return false;
		}
		
		return true;
	}

	public function order_status_changed($order_id) {

        
		$order = wc_get_order( $order_id );
		if ($order->has_status(get_option( 'ecomkassa_fiscalize_on_order_status_sell' ))) {
			$this->fiscalize($order_id, 'sell');
		} elseif ($order->has_status(get_option( 'ecomkassa_fiscalize_on_order_status_sell_refund' ))) {
			$this->fiscalize($order_id, 'sell_refund');
		}
	}

	public function fiscalize( $order_id, $operation_name ) {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$method                   = $order->get_payment_method();
		$available_payment_method = $this->checkPaymentMethod( $method );
		if ( ! $available_payment_method ) {
			return;
		}

		$tax_system = intval( get_option( 'ecomkassa_tax_system' ) );

		$check = new Check( $order_id, $order->get_billing_email(), $operation_name, $tax_system );

		if ( sizeof( $order->get_items() ) > 0 ) {
			$vat_item = '';
			$vat_shipping = '';
			foreach ( $order->get_items( 'line_item' ) as $item ) {
				if (!wc_tax_enabled() || $item->get_tax_status() == 'none') {
					$vat_item = Vat::RATE_NO;
				} else {
					$item_tax = $order->get_item_tax( $item,true );
					$item_total = $order->get_item_total( $item, true, true );

					if ($item_tax == 0) {
						$vat_item = Vat::RATE_0;
					} elseif (round($item_total * (10/110), 2) == $item_tax) {
						$vat_item = Vat::RATE_10;
					} elseif (round($item_total * (18/118), 2) == $item_tax) {
						$vat_item = Vat::RATE_18;
					}
				}

				$check->addPosition( new Position(
					$item->get_name(),
					$order->get_item_total( $item, true, true ),
					$item->get_quantity(),
					$order->get_line_total( $item, true, true ),
					new Vat( $vat_item )
				) );
			}
			// shipping
			foreach ( $order->get_items( 'shipping' ) as $item ) {
				if (!wc_tax_enabled() || $item->get_tax_status() == 'none') {
					$vat_shipping = Vat::RATE_NO;
				} else {
					$item_tax = $order->get_item_tax( $item,true );
					$item_total = $order->get_item_total( $item, true, true );
					if ($item_tax == 0) {
						$vat_shipping = Vat::RATE_0;
					} elseif (round($item_total * (10/110), 2) == $item_tax) {
						$vat_shipping = Vat::RATE_10;
					} elseif (round($item_total * (18/118), 2) == $item_tax) {
						$vat_shipping = Vat::RATE_18;
					}
				}

				$check->addPosition( new Position(
					$item->get_name(),
					$order->get_item_total( $item, true, true ),
					$item->get_quantity(),
					$order->get_line_total( $item, true, true ),
					new Vat( $vat_shipping ), 'service'
				) );
			}
		}

		$check->addPayment( Payment::createCard( $order->get_total() ) );
       
		$error_message = "";
		$response      = null;
		try {
			$response = $this->queueManager->putCheck( $check, $operation_name );
			$order->add_order_note( 'Данные заказа были отправлены для регистрации чека прихода ');
		} catch ( ClientException $e ) {
			$error_message = $e->getMessage();
			$error_code    = $e->getCode();
		} catch ( ClientExceptionError $e ) {
			$error_message = $e->getMessage();
			$error_code    = $e->getCode();
		} catch ( ClientExceptionErrorIncomingMissingToken $e ) {
			$error_message = $e->getMessage();
			$error_code    = $e->getCode();
		} catch ( ClientExceptionErrorIncomingValidationException $e ) {
			$error_message = $e->getMessage();
			$error_code    = $e->getCode();
		} catch ( ClientExceptionErrorAuthWrongUserOrPassword $e ) {
			$error_message = $e->getMessage();
			$error_code    = $e->getCode();
		}

		do_action( 'ecom_kassa_report_create', intval( $order_id ), $operation_name, $check->asArray(), $response, $error_message );
		
		
	}

	public function handle_requests() {
		if (
			$_SERVER['REQUEST_METHOD'] == "POST" &&
			isset( $_REQUEST['ecomkassa'] )
			&& $_REQUEST['ecomkassa'] == 'callback'
		) {
			$body           = @file_get_contents( 'php://input' );
			$callbackParams = json_decode( $body, true );

			if ( ! json_last_error() && empty( $callbackParams['error'] ) ) {
				do_action( 'ecom_kassa_report_update', $callbackParams['status'], $callbackParams );

				// Сохранение чека в БД если статус заявки done
				if ( $callbackParams['status'] == 'done' ) {
					$this->check->save( $callbackParams );
				}
			} else {
				header( "HTTP/1.1 400 Bad Request" );
				header( "Status: 400 Bad Request" );
			}

			exit();
		}
	}

	public function report_create( $order_id, $operation_name, $request_check_data, $response_data, $error = "" ) {
		$this->report->create( $order_id, $operation_name, $request_check_data, $response_data, $error );
	}

	public function report_update( $state, $report_data ) {
		$this->report->update( $state, $report_data );
	}

	// Добавление metabox в заказ
	public function order_receipts_meta_boxes() {
		global $woocommerce, $order, $post;

		add_meta_box( 'mv_other_fields', 'Чеки', [
			$this,
			'show_order_receipts_meta_boxes_content'
		], 'shop_order', 'side', 'core' );
	}

	// добавление в metabox
	public function show_order_receipts_meta_boxes_content() {
		global $post;

		$checks = $this->check->get_receipts( $post->ID );

		if ( empty( $checks ) ) {
			return;
		}

		$html = '';
		foreach ( $checks as $check ) {
			$check_operation = '';

			switch ( $check->operation ) {
				case 'sell':
					$check_operation = 'приход';
					break;
				case 'sell_refund':
					$check_operation = 'возврат прихода';
					break;
			}

			$html .= '<h4>Чек #' . $check->internal_id . '</h4>';
			$html .= '<p> Дата и время: ' . $check->datetime . '</p>';
			$html .= '<p> Вид кассового чека: ' . $check_operation . '</p>';
			$html .= '<p> Итог по чеку: ' . $check->total . '</p>';
			$html .= '<p> Номер фискального накопителя: ' . $check->ecr_registration_number . '</p>';
			$html .= '<p> Номер фискального документа: ' . $check->fiscal_document_number . '</p>';
			$html .= '<p> Фискальный признак документа: ' . $check->fiscal_document_attribute . '</p>';
			$html .= '<hr>';
		}

		echo $html;
	}

	function add_order_check_meta_box_action( $actions ) {
		// add "mark printed" custom action
		$actions['wc_order_check_action_sell']        = 'Сформировать чек прихода';
		$actions['wc_order_check_action_sell_refund'] = 'Сформировать чек возврата приход';
		$actions['wc_order_check_action_buy']         = 'Сформировать чек расхода';
		$actions['wc_order_check_action_buy_refund']  = 'Сформировать чек возврата расхода';

		return $actions;
	}

	function wc_process_order_check_action_sell( $order ) {
		$this->fiscalize( $order->get_id(), 'sell' );
		$order->add_order_note( 'Данные заказа отправлена для формирования чека прихода' );
	}
	function wc_process_order_check_action_sell_refund( $order ) {
		$this->fiscalize( $order->get_id(), 'sell_refund' );
		$order->add_order_note( 'Данные заказа отправлена для формирования чека возврата прихода' );
	}
	function wc_process_order_check_action_buy( $order ) {
		$this->fiscalize( $order->get_id(), 'buy' );
		$order->add_order_note( 'Данные заказа отправлена для формирования чека расхода' );
	}
	function wc_process_order_check_action_buy_refund( $order ) {
		$this->fiscalize( $order->get_id(), 'buy_refund' );
		$order->add_order_note( 'Данные заказа отправлена для формирования чека возврата расхода ' );
	}
}

function Ecom_Kassa() {
	return EcomKassa::instance();
}

$GLOBALS['ecomkassa'] = Ecom_Kassa();

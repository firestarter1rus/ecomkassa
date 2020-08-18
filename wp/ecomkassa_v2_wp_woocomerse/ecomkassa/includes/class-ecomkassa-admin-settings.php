<?php

use Ecom\KassaSdk\Client;
use Ecom\KassaSdk\Exception\ClientExceptionErrorAuthWrongUserOrPassword;

final class EcomKassa_AdminSettings {

	private static $options = array(
		'ecomkassa_server_url'                              => 'string',
		'ecomkassa_shop_id'                                 => 'string',
		'ecomkassa_login'                                   => 'string',
		'ecomkassa_password'                                => 'string',
		'ecomkassa_tax_system'                              => 'integer',
		'ecomkassa_fiscalize_on_order_status_sell'          => 'string',
		'ecomkassa_fiscalize_on_order_status_sell_refund'   => 'string',
		'ecomkassa_fiscalize_on_available_payment_gateways' => 'array',
	);

	public static function out() {
		if ( ! empty( $_POST ) ) {
			self::save();
		}



//		var_dump(wc_tax_enabled());
//		var_dump(wc_prices_include_tax());

		include( ECOMKASSA_ABSPATH_VIEWS . 'html-admin-settings.php' );
	}

	public static function show_connection_notice( $code ) {
		$message = '';
		$class   = '';
		switch ( $code ) {
			case 0:
				$message = 'Соединение установлено!';
				$class   = 'updated';
				break;
			case 1:
				$message = 'Неверный логин или пароль ';
				$class   = 'error';
				break;
			case 2:
				$message = 'Не верно указаны настройки';
				$class   = 'error';
				break;
		}
		?>
        <div class="<?= $class ?> notice"><p><?= $message ?></p></div>
		<?php
	}

	public static function save() {

		foreach ( self::$options as $key => $type ) {
			$value = filter_input( INPUT_POST, $key );

			if ( $type == 'string' ) {
				update_option( $key, $value );
			} else if ( $type == 'bool' ) {
				update_option( $key, $value === "1" ? "1" : "0" );
			} else if ( $type == 'integer' ) {
				update_option( $key, intval( $value ) );
			} else if ( $type == 'array' ) {
				update_option( $key, $_POST[ $key ] );
			}
		}

		$client = new Client( '', $_POST['ecomkassa_login'], $_POST['ecomkassa_password'] );
		$client->setHost( $_POST['ecomkassa_server_url'] );
		
		try {
			$client->getToken();
			self::show_connection_notice( 0 );
		} catch ( ClientExceptionErrorAuthWrongUserOrPassword $e ) {
			self::show_connection_notice( 1 );
		} catch ( Exception $e ) {
			self::show_connection_notice( 2 );
		}
	}
}
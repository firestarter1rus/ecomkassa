<?php

/**
 * This file is part of the ecom/kassa-sdk library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecom\KassaSdk;

use Ecom\KassaSdk\Exception\ClientException;
use Ecom\KassaSdk\Exception\ClientExceptionError;
use Ecom\KassaSdk\Exception\ClientExceptionErrorIncomingMissingToken;
use Ecom\KassaSdk\Exception\ClientExceptionErrorIncomingValidationException;
use Ecom\KassaSdk\Exception\ClientExceptionErrorAuthWrongUserOrPassword;

class Client {
	/**
	 * @var string
	 */
	private $host = 'https://app.ecomkassa.ru';

	/**
	 * @var string
	 */
	private $login;
	private $password;
	private $key;


	/**
	 * Client constructor.
	 *
	 * @param $key
	 * @param $login
	 * @param $password
	 */
	public function __construct($key, $login, $password ) {
		$this->key      = $key;
		$this->login    = $login;
		$this->password = $password;
	}

	/**
	 * @param string $value
	 *
	 * @return Client
	 */
	public function setHost( $value ) {
		$this->host = $value;

		return $this;
	}

	public function getToken() {
		$url = $this->host . "/getToken";

		$data = [
			'login' => $this->login,
			'pass'  => $this->password
		];

		$data      = json_encode( $data );
		$headers[] = 'Content-Type: application/json; charset=utf-8';
		$method    = 'POST';

		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		if ( $method == 'POST' ) {
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		}
		$response = curl_exec( $ch );
		$error    = null;
		if ( $response === false ) {
			$error = curl_error( $ch );
		} else {
			$status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			if ( $status !== 200 ) {
				$error = sprintf( 'Unexpected status (%s)', $status );
			}
		}
		curl_close( $ch );
		
		if ( $error !== null ) {
			throw new ClientException( $error );
		}

		$response = json_decode( $response, true );



		/*if ($response['code'] != 0){
			throw new ClientExceptionErrorAuthWrongUserOrPassword('Неверный логин или пароль');
		}*/

		$token = $response['token'];

		return $token;
	}

	/**
	 * @param string $path
	 * @param mixed $data
	 *
	 * @return mixed
	 */
	public function sendRequest( $path, $data = null ) {

		$data = json_encode( $data );

		$method = 'POST';

		$token = $this->getToken();

		$url = sprintf( '%s/%s', $this->host, $path );


		if ( $method == 'POST' ) {
			$headers = array('Content-Type: application/json; charset=utf-8',
			"Token: $token");
		}

		$ch = curl_init( $url );
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		if ( $method == 'POST' ) {
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
		}
		$response = curl_exec( $ch );
		$error    = null;
		if ( $response === false ) {
			$error = curl_error( $ch );
		} else {
			$status = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
			if ( $status !== 200 ) {
				$error = sprintf( 'Unexpected status (%s)', $status );
			}
		}
		curl_close( $ch );
		if ( $error !== null ) {
			throw new ClientException( $error );
		}

		$response = json_decode( $response, true );

		if ( ! empty( $response['error'] ) ) {
			if ( $response['error']['code'] == 1 ) {
				throw new ClientExceptionError( 'Неклассифицированная ошибка', 1 );
			} elseif ($response['error']['code'] == 4) {
				throw new ClientExceptionErrorIncomingMissingToken( 'Запрос к защищённому ресурсу произведён без токена', 4 );
			} elseif ($response['error']['code'] == 8) {
				throw new ClientExceptionErrorIncomingValidationException( 'Указанный запрос имеет некорректную структуру', 8 );
			} elseif ($response['error']['code'] == 19) {
				throw new ClientExceptionErrorAuthWrongUserOrPassword( 'Неправильные логин или пароль пользователя', 19 );
			}
		}

		return $response;
	}
}

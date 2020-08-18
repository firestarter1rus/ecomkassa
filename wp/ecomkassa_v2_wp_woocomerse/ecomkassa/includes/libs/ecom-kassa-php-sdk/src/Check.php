<?php

/**
 * This file is part of the ecom/kassa-sdk library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecom\KassaSdk;

class Check
{
	const INTENT_SELL = 'sell';
	const INTENT_SELL_RETURN = 'sell_refund';

	/**
	 * Common tax system
	 */
	const TS_COMMON = 0;

	/**
	 * Simplified tax system: Income
	 */
	const TS_SIMPLIFIED_IN = 1;

	/**
	 * Simplified tax system: Income - Outgo
	 */
	const TS_SIMPLIFIED_IN_OUT = 2;

	/**
	 * An unified tax on imputed income
	 */
	const TS_UTOII = 3;

	/**
	 * Unified social tax
	 */
	const TS_UST = 4;

	/**
	 * Patent
	 */
	const TS_PATENT = 5;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var string
	 */
	private $email;

	/**
	 * @var string
	 */
	private $intent;
    /**
	 * @var string
	 */
	private $inn;
   	
	/**
	 * @var int
	 */
	private $taxSystem;

	/**
	 * @var bool
	 */
	private $shouldPrint = false;

	/**
	 * @var Payment[]
	 */
	private $payments = [];

	/**
	 * @var Position[]
	 */
	private $positions = [];

	/**
	 * @param string $id An unique ID provided by an online store
	 * @param string $email User E-Mail
	 * @param string $intent Check::INTENT_SELL or Check::INTENT_SELL_RETURN
	 * @param int    $taxSystem See Check::TS_*
	 * @param string $inn
	 *
	 *
	 * @return Check
	 */
	public function __construct($id, $email, $intent, $taxSystem, $inn = '7708317992')
	{
		$this->id = strval($id . uniqid('_', true));
		$this->email = $email;
		$this->intent = $intent;
		$this->taxSystem = $taxSystem;
		$this->inn = $inn;
	}

	/**
	 * @param string $id
	 * @param string $email
	 * @param int    $taxSystem
	 *
	 * @return Check
	 */
	public static function createSell($id, $email, $taxSystem, $inn)
	{
		return new static($id, $email, static::INTENT_SELL, $taxSystem, $inn);
	}

	/**
	 * @param string $id
	 * @param string $email
	 * @param int    $taxSystem
	 *
	 * @return Check
	 */
	public static function createSellReturn($id, $email, $taxSystem, $inn)
	{
		return new static($id, $email, static::INTENT_SELL_RETURN, $taxSystem, $inn);
	}

	/**
	 * @param bool $value
	 *
	 * @return Check
	 */
	public function setShouldPrint($value)
	{
		$this->shouldPrint = (bool) $value;

		return $this;
	}

	/**
	 * @param Payment $payment
	 *
	 * @return Check
	 */
	public function addPayment(Payment $payment)
	{
		$this->payments[] = $payment;

		return $this;
	}

	/**
	 * @param Position $position
	 *
	 * @return Check
	 */
	public function addPosition(Position $position)
	{
		$this->positions[] = $position;

		return $this;
	}

	public function getTax($taxNumber)
	{
		switch (intval($taxNumber)) {
			case 0:
				return 'osn';
			case 1:
				return 'usn_income';
			case 2:
				return 'usn_income_outcome';
			case 3:
				return 'envd';
			case 4:
				return 'esn';
			case 5:
				return 'patent';
		}

		return 'osn';
	}

	/**
	 * @return array
	 */
	public function asArray()
	{
		$payments = array_map(
			function ($payment) {
				return $payment->asArray();
			},
			$this->payments
		);

		$total = $payments[0]['sum'];

		$callback_url = get_site_url().'/?ecomkassa=callback';

		return [
			'external_id' => $this->id,
			'receipt' => [
			    'client' => [
			        'email' => $this->email,
			    ],
				'company' => [
					'email' => $this->email,
					'sno'   => $this->getTax($this->taxSystem),
					'inn'             => $this->inn,
				    'payment_address' => get_site_url(),
				],
				'items'      => array_map(
					function ($position) {
						return $position->asArray();
					},
					$this->positions
				),
				'payments' => $payments,
				'vats' => [],
				'total'    => floatval($total)
			],
			'service'     => [
				'callback_url'    => $callback_url,
				
			],
			'timestamp'   => date('d.m.Y H:i:s')
		];
	}
}

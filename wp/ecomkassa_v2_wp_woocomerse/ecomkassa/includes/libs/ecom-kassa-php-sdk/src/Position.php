<?php

/**
 * This file is part of the ecom/kassa-sdk library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecom\KassaSdk;

class Position
{
    const PM_FULL_PREPAYMENT = 0;
    const PM_PREPAYMENT = 1;
    const PM_ADVANCE = 2;
    const PM_FULL_PAYMENT = 3;
    const PM_PARTIAL_PAYMENT = 4;
    const PM_CREDIT = 5;
    const PM_CREDIT_PAYMENT = 6;
    /**
     * @var string
     */
    private $name;

    /**
     * @var int|float
     */
    private $price;

    /**
     * @var int|float
     */
    private $quantity;

    /**
     * @var int|float
     */
    private $total;

    /**
     * @var Vat
     */
    private $vat;

    /**
     * @var string
     */
    private $payment_object;
    
    /**
     * @var int
     */
    private $payment_method;
    
    /**
     * @param string $name Item name
     * @param int|float $price Item price
     * @param int|float $quantity Item quanitity
     * @param int|float $total Total cost
     * @param int|float $discount Discount size in RUB
     * @param Vat $vat VAT
     * @param string $payment_object Payment object 'comodity' or 'service'
     * @param int $payment_method Payment method full_payment
     *
     * @return Position
     */
    public function __construct($name, $price, $quantity, $total, Vat $vat, $payment_object="commodity", $payment_method=3)
    {
        $this->name = $name;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->total = $total;
        $this->vat = $vat;
        if (in_array($payment_object, array("commodity","excise","job","service","gambling_bet","gambling_prize","lottery","lottery_prize","intellectual_activity","payment","agent_commission","composite","another")) )
            $this->payment_object = $payment_object;
        else
            throw new Exception('Invalid payment object');
      //  if (in_array($payment_method, array("full_prepayment","prepayment","advance","full_payment","partial_payment","credit","credit_payment")) )
            $this->payment_method = $payment_method;
       // else
          //  throw new Exception('Invalid payment method');
    }
    
    public function getPaymentMethod($pm)
    {
        switch (intval($pm)) {
            case 0: return "full_prepayment";
            case 1: return "prepayment";
            case 2: return "advance";
            case 3: return "full_payment";
            case 4: return "partial_payment";
            case 5: return "credit";
            case 6: return "credit_payment";
        }
        return "full_payment";
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'sum' => $this->total,
            'vat' => ['type' => $this->vat->getRate()],
            'payment_object' => $this->payment_object,
            'payment_method' => $this->getPaymentMethod($this->payment_method),
        ];
    }
}

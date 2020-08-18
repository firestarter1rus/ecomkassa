<?php

/**
 * This file is part of the ecom/kassa-sdk library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ecom\KassaSdk;

class Vat
{
    /**
     * Without VAT
     */
    const RATE_NO = 'none';

    /**
     * 0%
     */
    const RATE_0 = 'vat0';

    /**
     * 10%
     */
    const RATE_10 = 'vat10';

    /**
     * 18%
     */
    const RATE_18 = 'vat18';

    /**
     * 10/110
     */
    const RATE_110 = 'vat110';

    /**
     * 18/118
     */
    const RATE_118 = 'vat118';

    private $rate;

    /**
     * @param string|int|float $rate See Vat::RATE_*
     *
     * @return Vat
     */
    public function __construct($rate)
    {
        if (!is_string($rate)) {
            $rate = (string) $rate;
        }

        $rate = str_replace(['0.', '%'], '', $rate);

        switch ($rate) {
            case '10/110':
                $rate = static::RATE_110;
                break;
            case '18/118':
                $rate = static::RATE_118;
                break;
            default:
                if (!in_array($rate, [
                    static::RATE_NO,
                    static::RATE_0,
                    static::RATE_10,
                    static::RATE_18,
                    static::RATE_110,
                    static::RATE_118,
                ])) {
                    throw new \InvalidArgumentException(sprintf('Unknown VAT rate: %s', $rate));
                }
        }

        $this->rate = $rate;
    }

    /**
     * @return string
     */
    public function getRate()
    {
        return $this->rate;
    }
}

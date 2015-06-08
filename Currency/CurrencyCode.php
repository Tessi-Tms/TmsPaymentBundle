<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Currency;

class CurrencyCode
{
    // ISO 4217
    public static $CODE_MAP = array(
        'EUR' => '978',
        'USD' => '840',
        'CHF' => '756',
        'GBP' => '826',
        'CAD' => '124',
        'JPY' => '392',
        'MXN' => '484',
        'TRY' => '949',
        'AUD' => '036',
        'NZD' => '554',
        'NOK' => '578',
        'BRL' => '986',
        'ARS' => '032',
        'KHR' => '116',
        'TWD' => '901',
        'SEK' => '752',
        'DKK' => '208',
        'KRW' => '410',
        'SGD' => '702',
        'XPF' => '953',
        'XOF' => '952',
    );

    /**
     * Returns alphabetic codes.
     *
     * @return array Alphabetic codes.
     */
    public static function getAlphabeticCodes()
    {
        return array_keys(self::$CODE_MAP);
    }

    /**
     * Returns the alphabetic code associated with the given numeric code.
     *
     * @param string $numeric The numeric code.
     *
     * @return string The alphabetic code.
     */
    public static function getAlphabeticCode($numeric)
    {
        $flip = array_flip(self::$CODE_MAP);

        return $flip[$numeric];
    }

    /**
     * Returns numeric codes.
     *
     * @return array Numeric codes.
     */
    public static function getNumericCodes()
    {
        return array_values(self::$CODE_MAP);
    }

    /**
     * Returns the numeric code associated with the given alphabetic code.
     *
     * @param string $alphabetic The alphabetic code.
     *
     * @return string The numeric code.
     */
    public static function getNumericCode($alphabetic)
    {
        return self::$CODE_MAP[$alphabetic];
    }
}


<?php

namespace App\Service;

class CurrencyChecker
{
    public const EUR = 'EUR';

    private const COUNTRIES_IN_EURO = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT',
        'LU', 'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
    ];

    public function isEuro(string $countryCode): bool
    {
        if (in_array($countryCode, self::COUNTRIES_IN_EURO)) {
            return true;
        }
        return false;
    }
}
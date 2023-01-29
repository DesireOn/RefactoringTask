<?php

namespace App\Service;

class CurrencyCalculator
{
    private CurrencyChecker $currencyChecker;

    /**
     * @param CurrencyChecker $currencyChecker
     */
    public function __construct(CurrencyChecker $currencyChecker)
    {
        $this->currencyChecker = $currencyChecker;
    }

    /**
     * @param string $country
     * @param float $amountFixed
     * @return float
     */
    public function calculate(string $country, float $amountFixed): float
    {
        $isEuro = $this->currencyChecker->isEuro($country);
        if ($isEuro) {
            $result = 0.01 * $amountFixed;
        } else {
            $result = 0.02 * $amountFixed;
        }

        return round($result, 2);
    }
}
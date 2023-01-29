<?php

namespace App\Factory;

use App\External\CurrencyRatesProviderInterface;
use App\External\ExchangeRates;
use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyRatesProviderFactory
{
    /**
     * @param string $type
     * @param HttpClientInterface $param
     * @return ExchangeRates
     * @throws Exception
     */
    public static function createCurrencyRatesProvider(string $type, HttpClientInterface $param): ExchangeRates
    {
        switch ($type) {
            case CurrencyRatesProviderInterface::EXCHANGE_RATES:
                return new ExchangeRates($param);
            default:
                throw new Exception('Invalid bin provider type');
        }
    }
}
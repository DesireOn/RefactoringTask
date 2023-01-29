<?php

namespace App\External;

use App\Exception\ExchangeRatesException;
use App\Model\Transaction;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

interface CurrencyRatesProviderInterface
{
    public const EXCHANGE_RATES = 'exchange_rates';
    /**
     * @param Transaction $transaction
     * @return float
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ExchangeRatesException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getAmountFixed(Transaction $transaction): float;
}
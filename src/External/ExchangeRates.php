<?php

namespace App\External;

use App\Exception\ExchangeRatesException;
use App\Model\Transaction;
use App\Service\CurrencyChecker;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ExchangeRates
{
    private const URL = 'https://api.apilayer.com/exchangerates_data/latest';
    private const KEY = 'uoQVKMVWsKrjIrj5HEwHjZV6UR7nAXD4'; // Needs to be in .env file
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

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
    public function getAmountFixed(Transaction $transaction): float
    {
        $currency = $transaction->getCurrency();
        $amount = $transaction->getAmount();

        $content = $this->getResults();
        if ($content['success'] === false) {
            throw new ExchangeRatesException('Success status from Exchange Rates API is false.');
        }

        $rate = 0.00;
        if (
            isset($content['rates']) &&
            isset($content['rates'][$currency]) &&
            is_float($content['rates'][$currency])
        ) {
            $rate = (float)$content['rates'][$currency];
        }

        if ($currency !== CurrencyChecker::EUR && $rate > 0) {
            $amount /= $rate;
        }

        return round($amount,2);
    }

    /**
     * @return mixed[]
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ExchangeRatesException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function getResults(): array
    {
        $response = $this->httpClient->request('GET', self::URL, [
            'query' => [
                'apikey' => self::KEY
            ]
        ]);

        $responseStatusCode = $response->getStatusCode();
        if ($responseStatusCode !== 200) {
            throw new ExchangeRatesException('The Exchange Rates API gives status code: '.$responseStatusCode);
        }

        return $response->toArray();
    }
}
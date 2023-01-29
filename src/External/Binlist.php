<?php

namespace App\External;

use App\Exception\BinProviderException;
use App\Model\Transaction;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Binlist implements BinProviderInterface
{
    private const URL = 'https://lookup.binlist.net/';
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param Transaction $transaction
     * @return string
     * @throws BinProviderException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function getCountry(Transaction $transaction): string
    {
        $response = $this->httpClient->request(
            'GET',
            self::URL.$transaction->getBin()
        );

        $responseStatusCode = $response->getStatusCode();
        if ($responseStatusCode !== 200) {
            throw new BinProviderException('The Binlist API gives status code: '.$responseStatusCode);
        }

        $binResults = $response->toArray();
        if (isset($binResults['country']) || isset($binResults['country']['alpha2'])) {
            return $binResults['country']['alpha2'];
        }

        throw new BinProviderException(
            'The country code has not been retrieved successfully from from Binlist API.'
        );
    }
}
<?php

namespace External;

use App\Exception\BinlistException;
use App\External\Binlist;
use App\Model\Transaction;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BinlistTest extends TestCase
{

    /**
     * @return void
     * @throws BinlistException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testThrowingExceptionWhenApiDoesNotReturnStatusCode200(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->method('getStatusCode')
            ->willReturn(500);

        $mockHttpClient
            ->method('request')
            ->willReturn($mockResponse);

        $binlist = new Binlist($mockHttpClient);
        $transaction = new Transaction('516793', 50.00, 'USD');

        self::expectException(BinlistException::class);
        self::expectExceptionMessage('The Binlist API gives status code: 500');
        $binlist->getCountry($transaction);
    }

    /**
     * @return void
     * @throws BinlistException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testThrowingExceptionWhenResponseDoesNotContainCountryCode(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->method('getStatusCode')
            ->willReturn(200);
        $mockResponse
            ->method('toArray')
            ->willReturn([]);

        $mockHttpClient
            ->method('request')
            ->willReturn($mockResponse);

        $binlist = new Binlist($mockHttpClient);
        $transaction = new Transaction('516793', 50.00, 'USD');

        self::expectException(BinlistException::class);
        self::expectExceptionMessage('The country code has not been retrieved successfully from from Binlist API.');
        $binlist->getCountry($transaction);
    }

    /**
     * @return void
     * @throws BinlistException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetCountryReturnsCountryCodeFromApi(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->method('getStatusCode')
            ->willReturn(200);
        $mockResponse
            ->method('toArray')
            ->willReturn([
                "number" => [
                ],
                "scheme" => "mastercard",
                "type" => "debit",
                "brand" => "Debit",
                "country" => [
                    "numeric" => "440",
                    "alpha2" => "LT",
                    "name" => "Lithuania",
                    "emoji" => "🇱🇹",
                    "currency" => "EUR",
                    "latitude" => 56,
                    "longitude" => 24
                ],
                "bank" => [
                ]
            ]);

        $mockHttpClient
            ->method('request')
            ->willReturn($mockResponse);

        $binlist = new Binlist($mockHttpClient);
        $transaction = new Transaction('516793', 50.00, 'USD');

        $country = $binlist->getCountry($transaction);
        self::assertSame( 'LT', $country);
    }
}
<?php

namespace External;

use App\Exception\ExchangeRatesException;
use App\External\ExchangeRates;
use App\Model\Transaction;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ExchangeRatesTest extends TestCase
{
    /**
     * @return void
     * @throws ExchangeRatesException
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

        $exchangeRates = new ExchangeRates($mockHttpClient);
        $transaction = new Transaction('516793', 50.00, 'USD');

        self::expectException(ExchangeRatesException::class);
        self::expectExceptionMessage('The Exchange Rates API gives status code: 500');
        $exchangeRates->getAmountFixed($transaction);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ExchangeRatesException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testThrowingExceptionWhenStatusIsNotSuccess(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->method('getStatusCode')
            ->willReturn(200);
        $mockResponse
            ->method('toArray')
            ->willReturn([
                "success" => false,
                "timestamp" => 1519296206,
                "base" => "EUR",
                "date" => "2021-03-17",
                "rates" => [
                    "AUD" => 1.566015,
                    "CAD" => 1.560132,
                    "CHF" => 1.154727,
                    "CNY" => 7.827874,
                    "GBP" => 0.882047,
                    "JPY" => 132.360679,
                    "USD" => 1.23396
                ]
            ]);

        $mockHttpClient
            ->method('request')
            ->willReturn($mockResponse);

        $exchangeRates = new ExchangeRates($mockHttpClient);
        $transaction = new Transaction('516793', 50.00, 'USD');

        self::expectException(ExchangeRatesException::class);
        self::expectExceptionMessage('Success status from Exchange Rates API is false.');
        $exchangeRates->getAmountFixed($transaction);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ExchangeRatesException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetAmountFixedWhenThereIsRate(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->method('getStatusCode')
            ->willReturn(200);
        $mockResponse
            ->method('toArray')
            ->willReturn([
                "success" => true,
                "timestamp" => 1519296206,
                "base" => "EUR",
                "date" => "2021-03-17",
                "rates" => [
                    "AUD" => 1.566015,
                    "CAD" => 1.560132,
                    "CHF" => 1.154727,
                    "CNY" => 7.827874,
                    "GBP" => 0.882047,
                    "JPY" => 132.360679,
                    "USD" => 1.23396
                ]
            ]);

        $mockHttpClient
            ->method('request')
            ->willReturn($mockResponse);

        $exchangeRates = new ExchangeRates($mockHttpClient);
        $transaction = new Transaction('516793', 50.00, 'USD');

        $amount = $exchangeRates->getAmountFixed($transaction);
        self::assertSame( 40.52, $amount);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ExchangeRatesException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetAmountFixedWhenThereIsNoRate(): void
    {
        $mockHttpClient = $this->createMock(HttpClientInterface::class);
        $mockResponse = $this->createMock(ResponseInterface::class);

        $mockResponse
            ->method('getStatusCode')
            ->willReturn(200);
        $mockResponse
            ->method('toArray')
            ->willReturn([
                "success" => true,
                "timestamp" => 1519296206,
                "base" => "EUR",
                "date" => "2021-03-17",
                "rates" => [
                    "AUD" => 1.566015,
                    "CAD" => 1.560132,
                    "CHF" => 1.154727,
                    "CNY" => 7.827874,
                    "GBP" => 0.882047,
                    "JPY" => 132.360679,
                    "USD" => 1.23396
                ]
            ]);

        $mockHttpClient
            ->method('request')
            ->willReturn($mockResponse);

        $exchangeRates = new ExchangeRates($mockHttpClient);
        $transaction = new Transaction('516793', 50.00, 'BG');

        $amount = $exchangeRates->getAmountFixed($transaction);
        self::assertSame( 50.0, $amount);
    }
}
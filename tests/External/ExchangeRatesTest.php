<?php

namespace External;

use App\Exception\CurrencyRatesProviderException;
use App\External\CurrencyRatesProviderInterface;
use App\Factory\CurrencyRatesProviderFactory;
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
    private HttpClientInterface $mockHttpClient;
    private ResponseInterface $mockResponse;
    private CurrencyRatesProviderInterface $currencyRatesProvider;
    protected function setUp(): void
    {
        $this->mockHttpClient = $this->createMock(HttpClientInterface::class);
        $this->mockResponse = $this->createMock(ResponseInterface::class);
        $this->currencyRatesProvider = CurrencyRatesProviderFactory::createCurrencyRatesProvider(
            CurrencyRatesProviderInterface::EXCHANGE_RATES, $this->mockHttpClient
        );
    }

    /**
     * @return void
     * @throws CurrencyRatesProviderException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testThrowingExceptionWhenApiDoesNotReturnStatusCode200(): void
    {
        $this->mockResponse
            ->method('getStatusCode')
            ->willReturn(500);

        $this->mockHttpClient
            ->method('request')
            ->willReturn($this->mockResponse);

        $transaction = new Transaction('516793', 50.00, 'USD');

        self::expectException(CurrencyRatesProviderException::class);
        self::expectExceptionMessage('The Exchange Rates API gives status code: 500');
        $this->currencyRatesProvider->getAmountFixed($transaction);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws CurrencyRatesProviderException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testThrowingExceptionWhenStatusIsNotSuccess(): void
    {
        $this->mockResponse
            ->method('getStatusCode')
            ->willReturn(200);
        $this->mockResponse
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

        $this->mockHttpClient
            ->method('request')
            ->willReturn($this->mockResponse);

        $transaction = new Transaction('516793', 50.00, 'USD');

        self::expectException(CurrencyRatesProviderException::class);
        self::expectExceptionMessage('Success status from Exchange Rates API is false.');
        $this->currencyRatesProvider->getAmountFixed($transaction);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws CurrencyRatesProviderException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetAmountFixedWhenThereIsRate(): void
    {
        $this->mockResponse
            ->method('getStatusCode')
            ->willReturn(200);
        $this->mockResponse
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

        $this->mockHttpClient
            ->method('request')
            ->willReturn($this->mockResponse);

        $transaction = new Transaction('516793', 50.00, 'USD');

        $amount = $this->currencyRatesProvider->getAmountFixed($transaction);
        self::assertSame( 40.52, $amount);
    }

    /**
     * @return void
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws CurrencyRatesProviderException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testGetAmountFixedWhenThereIsNoRate(): void
    {
        $this->mockResponse
            ->method('getStatusCode')
            ->willReturn(200);
        $this->mockResponse
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

        $this->mockHttpClient
            ->method('request')
            ->willReturn($this->mockResponse);

        $transaction = new Transaction('516793', 50.00, 'BG');

        $amount = $this->currencyRatesProvider->getAmountFixed($transaction);
        self::assertSame( 50.0, $amount);
    }
}
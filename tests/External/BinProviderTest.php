<?php

namespace External;

use App\Exception\BinlistException;
use App\External\Binlist;
use App\External\BinProviderInterface;
use App\Factory\BinProviderFactory;
use App\Model\Transaction;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BinProviderTest extends TestCase
{
    private HttpClientInterface $mockHttpClient;
    private ResponseInterface $mockResponse;
    private BinProviderInterface $binProvider;

    /**
     * @return void
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->mockHttpClient = $this->createMock(HttpClientInterface::class);
        $this->mockResponse = $this->createMock(ResponseInterface::class);
        $this->binProvider = BinProviderFactory::createBinProvider(
            BinProviderInterface::BINLIST, $this->mockHttpClient
        );
    }

    /**
     * @return void
     * @throws BinlistException
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws Exception
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

        self::expectException(BinlistException::class);
        self::expectExceptionMessage('The Binlist API gives status code: 500');
        $this->binProvider->getCountry($transaction);
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
        $this->mockResponse
            ->method('getStatusCode')
            ->willReturn(200);
        $this->mockResponse
            ->method('toArray')
            ->willReturn([]);

        $this->mockHttpClient
            ->method('request')
            ->willReturn($this->mockResponse);

        $transaction = new Transaction('516793', 50.00, 'USD');

        self::expectException(BinlistException::class);
        self::expectExceptionMessage('The country code has not been retrieved successfully from from Binlist API.');
        $this->binProvider->getCountry($transaction);
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
        $this->mockResponse
            ->method('getStatusCode')
            ->willReturn(200);
        $this->mockResponse
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

        $this->mockHttpClient
            ->method('request')
            ->willReturn($this->mockResponse);

        $transaction = new Transaction('516793', 50.00, 'USD');

        $country = $this->binProvider->getCountry($transaction);
        self::assertSame( 'LT', $country);
    }
}
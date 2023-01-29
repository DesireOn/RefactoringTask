<?php

use App\Exception\CommandException;
use App\External\BinProviderInterface;
use App\External\CurrencyRatesProviderInterface;
use App\Factory\BinProviderFactory;
use App\Factory\CurrencyRatesProviderFactory;
use App\Processor;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProcessorIntegrationTest extends TestCase
{
    /**
     * @return void
     * @throws CommandException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testMissingFileNameExceptionMessage(): void
    {
        $processor = $this->createProcessor();

        self::expectException(CommandException::class);
        self::expectExceptionMessage('File is missing.');
        $processor->execute('');
    }

    /**
     * @return void
     * @throws CommandException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testFileDoesNotExistExceptionMessage(): void
    {
        $processor = $this->createProcessor();

        self::expectException(CommandException::class);
        self::expectExceptionMessage('File: test.txt does not exist.');
        $processor->execute('test.txt');
    }

    /**
     * @return void
     * @throws CommandException
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function testCommandReturnsExpectedOutput(): void
    {
        $processor = $this->createProcessor();

        $result = $processor->execute('input.txt');

        self::assertTrue($result);
    }

    /**
     * @return Processor
     * @throws DependencyException
     * @throws NotFoundException
     * @throws Exception
     */
    private function createProcessor(): Processor
    {
        $container = new Container();
        $container->set(HttpClientInterface::class, HttpClient::create());
        $container->set(
            BinProviderInterface::class,
            BinProviderFactory::createBinProvider(
                BinProviderInterface::BINLIST,
                $container->get(HttpClientInterface::class)
            )
        );
        $container->set(
            CurrencyRatesProviderInterface::class,
            CurrencyRatesProviderFactory::createCurrencyRatesProvider(
                CurrencyRatesProviderInterface::EXCHANGE_RATES,
                $container->get(HttpClientInterface::class)
            )
        );
        return $container->get('App\Processor');
    }
}
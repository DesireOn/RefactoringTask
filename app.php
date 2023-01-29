<?php

use App\Exception\CommandException;
use App\External\BinProviderInterface;
use App\External\CurrencyRatesProviderInterface;
use App\Factory\BinProviderFactory;
use App\Factory\CurrencyRatesProviderFactory;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

require __DIR__.'/vendor/autoload.php';

$container = new Container();
$container->set(HttpClientInterface::class, HttpClient::create());
try {
    $container->set(
        BinProviderInterface::class,
        BinProviderFactory::createBinProvider(
            BinProviderInterface::BINLIST, $container->get(HttpClientInterface::class)
        )
    );
    $container->set(
        CurrencyRatesProviderInterface::class,
        CurrencyRatesProviderFactory::createCurrencyRatesProvider(
            CurrencyRatesProviderInterface::EXCHANGE_RATES,
            $container->get(HttpClientInterface::class)
        )
    );
} catch (DependencyException|NotFoundException $e) {
    echo "Error occurred during instantiating Binlist class: ".$e->getMessage();
} catch (Exception $e) {
}

try {
    $processor = $container->get('App\Processor');
} catch (DependencyException|NotFoundException $e) {
    echo "Error occurred during instantiating Processor class: ".$e->getMessage();
    return;
}

try {
    $fileName = $argv[1] ?? '';
    $processor->execute($fileName);
} catch (CommandException $e) {
    echo $e->getMessage();
    return;
}
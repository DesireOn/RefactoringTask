<?php

use App\Exception\CommandException;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

require __DIR__.'/vendor/autoload.php';

$container = new Container();
$container->set(HttpClientInterface::class, HttpClient::create());

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
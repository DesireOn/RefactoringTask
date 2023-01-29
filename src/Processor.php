<?php

namespace App;

use App\Exception\BinlistException;
use App\Exception\CommandException;
use App\Exception\ExchangeRatesException;
use App\Exception\TransactionGeneratorException;
use App\External\BinProviderInterface;
use App\External\ExchangeRates;
use App\Model\Transaction;
use App\Service\CurrencyCalculator;
use App\Service\TransactionGenerator;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class Processor
{
    private TransactionGenerator $transactionGenerator;
    private BinProviderInterface $binProvider;
    private ExchangeRates $exchangeRates;
    private CurrencyCalculator $calculator;

    public function __construct(
        TransactionGenerator $transactionGenerator,
        BinProviderInterface $binProvider,
        ExchangeRates $exchangeRates,
        CurrencyCalculator $calculator
    )
    {
        $this->transactionGenerator = $transactionGenerator;
        $this->binProvider = $binProvider;
        $this->exchangeRates = $exchangeRates;
        $this->calculator = $calculator;
    }


    /**
     * @param string $fileName
     * @return true
     * @throws CommandException
     */
    public function execute(string $fileName): bool
    {
        if ($fileName === '') {
            throw new CommandException('File is missing.');
        }
        if (!file_exists($fileName)) {
            throw new CommandException(sprintf('File: %s does not exist.', $fileName));
        }

        $fileContents = file_get_contents($fileName);
        if ($fileContents) {
            $fileContents = explode("\n", $fileContents);
            foreach ($fileContents as $row) {
                if (empty($row)) {
                    echo 'There is no row.';
                    continue;
                }

                echo "Processing transaction object...\n";
                $transaction = $this->processTransactionGenerator($row);
                if (is_null($transaction)) {
                    continue;
                }
                echo "Processing amount...\n";
                $amountFixed = $this->processAmountFixed($transaction);
                if (is_null($amountFixed)) {
                    continue;
                }
                echo "Processing country...\n";
                $country = $this->processCountry($transaction);
                if (is_null($country)) {
                    continue;
                }

                $finalAmount = $this->calculator->calculate($country, $amountFixed);
                echo "Result: ".number_format($finalAmount, 2, '.', '')."\n";
            }
        } else {
            throw new CommandException(sprintf("Can't read the following file: %s", $fileName));
        }

        return true;
    }

    /**
     * @param string $row
     * @return Transaction|null
     */
    private function processTransactionGenerator(string $row): ?Transaction
    {
        $transaction = null;
        try {
            $transaction = $this->transactionGenerator->generate($row);
        } catch (TransactionGeneratorException $e) {
            echo $e->getMessage();
        }

        return $transaction;
    }

    private function processAmountFixed(Transaction $transaction): ?float
    {
        $amountFixed = null;
        try {
            $amountFixed = $this->exchangeRates->getAmountFixed($transaction);
        } catch (ExchangeRatesException $e) {
            echo sprintf(
                "An error occurred during processing amount: %s\n",
                $e->getMessage()
            );
        } catch (
            ClientExceptionInterface|
            DecodingExceptionInterface|
            RedirectionExceptionInterface|
            ServerExceptionInterface|
            TransportExceptionInterface $e
        ) {
            echo "An error occurred during fetching data from Exchange Rates API.\n";
        }

        return $amountFixed;
    }

    /**
     * @param Transaction $transaction
     * @return string|null
     */
    private function processCountry(Transaction $transaction): ?string
    {
        $country = null;
        try {
            $country = $this->binProvider->getCountry($transaction);
        } catch (BinlistException $e) {
            echo sprintf(
                "An error occurred during processing country: %s\n",
                $e->getMessage()
            );
        } catch (
            ClientExceptionInterface|
            DecodingExceptionInterface|
            RedirectionExceptionInterface|
            ServerExceptionInterface|
            TransportExceptionInterface $e
        ) {
            echo "An error occurred during fetching data from BinList API.\n";
            // Log the message..
        }

        return $country;
    }
}
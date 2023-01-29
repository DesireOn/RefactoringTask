<?php

namespace Service;

use App\Exception\TransactionGeneratorException;
use App\Model\Transaction;
use App\Service\TransactionGenerator;
use PHPUnit\Framework\TestCase;

class TransactionGeneratorTest extends TestCase
{

    /**
     * @return void
     * @throws TransactionGeneratorException
     */
    public function testThrowingExceptionWhenIncorrectJson(): void
    {
        $row = '{"!bin":"45717360@","amount##"\:"100.00/","currency":"EUR"}';
        $transactionGenerator = new TransactionGenerator();

        self::expectException(TransactionGeneratorException::class);
        self::expectExceptionMessage('Json could not be parsed successfully.');
        $transactionGenerator->generate($row);
    }

    /**
     * @return void
     * @throws TransactionGeneratorException
     */
    public function testThrowingExceptionWhenBinIsEmptyString(): void
    {
        $row = '{"bin":"","amount":"100.00","currency":"EUR"}';
        $transactionGenerator = new TransactionGenerator();
        self::expectException(TransactionGeneratorException::class);
        self::expectExceptionMessage('BIN could not be generated.');
        $transactionGenerator->generate($row);
    }

    /**
     * @return void
     * @throws TransactionGeneratorException
     */
    public function testThrowingExceptionWhenBinIsMissing(): void
    {
        $row = '{"amount":"100.00","currency":"EUR"}';
        $transactionGenerator = new TransactionGenerator();
        self::expectException(TransactionGeneratorException::class);
        self::expectExceptionMessage('BIN could not be generated.');
        $transactionGenerator->generate($row);
    }

    /**
     * @return void
     * @throws TransactionGeneratorException
     */
    public function testTransactionModelIsBeingGeneratedSuccessfully(): void
    {
        $transactionGenerator = new TransactionGenerator();

        $row = '{"bin":"45717360","amount":"100.00","currency":"EUR"}';
        $transaction = $transactionGenerator->generate($row);

        self::assertInstanceOf( Transaction::class, $transaction);
        self::assertIsString($transaction->getBin());
        self::assertSame('45717360', $transaction->getBin());
        self::assertIsFloat($transaction->getAmount());
        self::assertSame(100.00, $transaction->getAmount());
        self::assertIsString($transaction->getCurrency());
        self::assertSame('EUR', $transaction->getCurrency());
    }
}
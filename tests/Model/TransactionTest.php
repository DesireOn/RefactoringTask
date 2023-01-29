<?php

namespace Model;

use App\Model\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @return void
     */
    public function testCanGetAndSetData(): void
    {
        $transaction = new Transaction('516793', 50.00, 'USD');

        self::assertSame('516793', $transaction->getBin());
        self::assertSame(50.00, $transaction->getAmount());
        self::assertSame('USD', $transaction->getCurrency());
    }

    /**
     * @return void
     */
    public function testOptionalParametersInConstructor(): void
    {
        $transaction = new Transaction('516793');

        self::assertSame('516793', $transaction->getBin());
        self::assertSame(0.00, $transaction->getAmount());
        self::assertSame(null, $transaction->getCurrency());
    }
}
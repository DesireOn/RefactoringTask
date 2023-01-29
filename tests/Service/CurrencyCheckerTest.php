<?php

namespace Service;

use App\Service\CurrencyChecker;
use PHPUnit\Framework\TestCase;

class CurrencyCheckerTest extends TestCase
{
    /**
     * @return void
     */
    public function testBgIsEuroCurrency(): void
    {
        $currencyChecker = new CurrencyChecker();
        $isEuro = $currencyChecker->isEuro('BG');
        self::assertTrue($isEuro, 'This is supposed to be an Euro currency');
    }

    /**
     * @return void
     */
    public function testUsIsNotEuCurrency(): void
    {
        $currencyChecker = new CurrencyChecker();
        $isEuro = $currencyChecker->isEuro('US');
        self::assertFalse($isEuro, 'This is not supposed to be an Euro currency');
    }
}
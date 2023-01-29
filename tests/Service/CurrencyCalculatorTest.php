<?php

namespace Service;

use App\Service\CurrencyCalculator;
use App\Service\CurrencyChecker;
use PHPUnit\Framework\TestCase;

class CurrencyCalculatorTest extends TestCase
{
    /**
     * @return void
     */
    public function testCalculatorMultipliesByOneWhenIsEuroIsTrue(): void
    {
        $mockCurrencyChecker = $this->createMock(CurrencyChecker::class);
        $mockCurrencyChecker
            ->method('isEuro')
            ->willReturn(true);

        $currencyCalculator = new CurrencyCalculator($mockCurrencyChecker);
        $result = $currencyCalculator->calculate('BG', 130);

        self::assertEquals(1.3, $result);
    }

    /**
     * @return void
     */
    public function testCalculatorMultipliesByTwoWhenIsEuroIsFalse(): void
    {
        $mockCurrencyChecker = $this->createMock(CurrencyChecker::class);
        $mockCurrencyChecker
            ->method('isEuro')
            ->willReturn(false);

        $currencyCalculator = new CurrencyCalculator($mockCurrencyChecker);
        $result = $currencyCalculator->calculate('US', 120);

        self::assertSame(2.4, $result);
    }
}
<?php

declare(strict_types=1);

namespace Tests;

use App\Exchanging\ValueObject\ExchangeAmount;
use App\Exchanging\ValueObject\ExchangeRateValue;
use Brick\Math\RoundingMode;
use PHPUnit\Framework\TestCase;

final class ExchangeDecimalPrecisionTest extends TestCase
{
    public function testMultiplicationPreservesValuesBeyondBinaryFloatPrecision(): void
    {
        $amount = ExchangeAmount::fromString('9007199254740993');
        $rate = ExchangeRateValue::fromString('1.0000000000');

        self::assertSame(
            '9007199254740993.0000000000',
            $amount->multiplyBy($rate, 10, RoundingMode::Down),
        );
    }

    public function testMultiplicationUsesExplicitScaleAndDownRounding(): void
    {
        $amount = ExchangeAmount::fromString('10.00');
        $rate = ExchangeRateValue::fromString('1.23456789019');

        self::assertSame(
            '12.3456789019',
            $amount->multiplyBy($rate, 10, RoundingMode::Down),
        );
    }
}

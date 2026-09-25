<?php

declare(strict_types=1);

namespace App\Exchanging\ValueObject;

use Brick\Math\BigDecimal;

final readonly class ExchangeRateValue
{
    public function __construct(private string $value)
    {
        if (1 !== preg_match('/^\d+(\.\d+)?$/', $value)) {
            throw new \InvalidArgumentException('Exchange rate value must be a positive decimal string.');
        }

        if (!BigDecimal::of($value)->isPositive()) {
            throw new \InvalidArgumentException('Exchange rate value must be greater than zero.');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}

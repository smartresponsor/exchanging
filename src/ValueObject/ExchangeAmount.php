<?php

declare(strict_types=1);

namespace App\Exchanging\ValueObject;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;

final readonly class ExchangeAmount
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if (!preg_match('/^\d+(\.\d+)?$/', $normalized)) {
            throw new \InvalidArgumentException('Exchange amount must be a positive decimal string.');
        }

        if (!BigDecimal::of($normalized)->isPositive()) {
            throw new \InvalidArgumentException('Exchange amount must be greater than zero.');
        }

        return new self($normalized);
    }

    public function multiplyBy(
        ExchangeRateValue $rate,
        int $scale = 10,
        RoundingMode $roundingMode = RoundingMode::Down,
    ): string {
        if ($scale < 0) {
            throw new \InvalidArgumentException('Exchange amount scale must be non-negative.');
        }

        return (string) BigDecimal::of($this->value)
            ->multipliedBy($rate->value())
            ->toScale($scale, $roundingMode);
    }

    public function value(): string
    {
        return $this->value;
    }
}

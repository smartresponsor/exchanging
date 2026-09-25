<?php

declare(strict_types=1);

namespace App\Exchanging\ValueObject;

final readonly class ExchangeCurrencyCode
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (!preg_match('/^[A-Z]{3}$/', $normalized)) {
            throw new \InvalidArgumentException('Currency code must be a three-letter ISO-like uppercase code.');
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }
}

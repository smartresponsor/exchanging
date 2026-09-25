<?php

declare(strict_types=1);

namespace App\Exchanging\ValueObject;

final readonly class ExchangeCurrencyPair
{
    public function __construct(
        private string $baseCurrencyCode,
        private string $quoteCurrencyCode,
    ) {
        $this->assertCurrencyCode($baseCurrencyCode, 'baseCurrencyCode');
        $this->assertCurrencyCode($quoteCurrencyCode, 'quoteCurrencyCode');
    }

    public static function fromStrings(string $baseCurrencyCode, string $quoteCurrencyCode): self
    {
        return new self($baseCurrencyCode, $quoteCurrencyCode);
    }

    public function baseCurrencyCode(): string
    {
        return strtoupper($this->baseCurrencyCode);
    }

    public function quoteCurrencyCode(): string
    {
        return strtoupper($this->quoteCurrencyCode);
    }

    public function key(): string
    {
        return $this->baseCurrencyCode() . '/' . $this->quoteCurrencyCode();
    }

    private function assertCurrencyCode(string $currencyCode, string $fieldName): void
    {
        if (1 !== preg_match('/^[A-Z]{3}$/', strtoupper($currencyCode))) {
            throw new \InvalidArgumentException(sprintf('%s must be an ISO-like three-letter currency code.', $fieldName));
        }
    }
}

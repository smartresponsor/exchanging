<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

use App\Exchanging\ValueObject\ExchangeAmount;
use App\Exchanging\ValueObject\ExchangeCurrencyPair;

final readonly class ExchangeQuoteRequestDTO
{
    public function __construct(
        private string $baseCurrencyCode,
        private string $quoteCurrencyCode,
        private string $amount,
        private ?\DateTimeImmutable $requestedForDate = null,
    ) {
        ExchangeCurrencyPair::fromStrings($baseCurrencyCode, $quoteCurrencyCode);

        ExchangeAmount::fromString($amount);
    }

    public function pair(): ExchangeCurrencyPair
    {
        return ExchangeCurrencyPair::fromStrings($this->baseCurrencyCode, $this->quoteCurrencyCode);
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function requestedForDate(): ?\DateTimeImmutable
    {
        return $this->requestedForDate;
    }
}

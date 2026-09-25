<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

use App\Exchanging\ValueObject\ExchangeCurrencyPair;

final readonly class ExchangeRateLookupRequestDTO
{
    public function __construct(
        private string $baseCurrencyCode,
        private string $quoteCurrencyCode,
        private ?\DateTimeImmutable $requestedForDate = null,
    ) {
        ExchangeCurrencyPair::fromStrings($baseCurrencyCode, $quoteCurrencyCode);
    }

    public function pair(): ExchangeCurrencyPair
    {
        return ExchangeCurrencyPair::fromStrings($this->baseCurrencyCode, $this->quoteCurrencyCode);
    }

    public function requestedForDate(): ?\DateTimeImmutable
    {
        return $this->requestedForDate;
    }
}

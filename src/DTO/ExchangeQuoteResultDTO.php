<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

use App\Exchanging\ValueObject\ExchangeCurrencyPair;

final readonly class ExchangeQuoteResultDTO
{
    public function __construct(
        private ExchangeCurrencyPair $pair,
        private string $sourceAmount,
        private string $rateValue,
        private string $convertedAmount,
        private string $providerCode,
        private \DateTimeImmutable $rateDate,
        private \DateTimeImmutable $capturedAtImmutable,
    ) {
    }

    public function pair(): ExchangeCurrencyPair
    {
        return $this->pair;
    }

    public function sourceAmount(): string
    {
        return $this->sourceAmount;
    }

    public function rateValue(): string
    {
        return $this->rateValue;
    }

    public function convertedAmount(): string
    {
        return $this->convertedAmount;
    }

    public function providerCode(): string
    {
        return $this->providerCode;
    }

    public function rateDate(): \DateTimeImmutable
    {
        return $this->rateDate;
    }

    public function capturedAtImmutable(): \DateTimeImmutable
    {
        return $this->capturedAtImmutable;
    }
}

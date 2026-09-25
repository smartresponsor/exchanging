<?php

declare(strict_types=1);

namespace App\Exchanging\Contract;

interface ExchangeRateEntityInterface
{
    public function id(): ?int;
    public function baseCurrencyCode(): string;
    public function quoteCurrencyCode(): string;
    public function rateValue(): string;
    public function providerCode(): string;
    public function rateDate(): \DateTimeImmutable;
    public function capturedAtImmutable(): \DateTimeImmutable;

    /** @return string ISO currency code boundary reference. */
    public function getSourceCurrency(): string;

    /** @return string ISO currency code boundary reference. */
    public function getTargetCurrency(): string;

    /** @return string Decimal exchange ratio. */
    public function getRatio(): string;
}

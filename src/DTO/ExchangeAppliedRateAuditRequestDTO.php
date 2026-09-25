<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeAppliedRateAuditRequestDTO
{
    public function __construct(
        public string $baseCurrencyCode,
        public string $quoteCurrencyCode,
        public string $sourceAmount,
        public string $convertedAmount,
        public string $rateValue,
        public string $providerCode,
        public \DateTimeImmutable $rateDate,
        public \DateTimeImmutable $capturedAtImmutable,
        public string $consumerContext,
        public string $consumerReference,
    ) {
    }
}

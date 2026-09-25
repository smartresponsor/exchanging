<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeFetchAndCaptureResultDTO
{
    public function __construct(
        public int $exchangeRateId,
        public string $baseCurrencyCode,
        public string $quoteCurrencyCode,
        public string $rateValue,
        public string $providerCode,
        public \DateTimeImmutable $rateDate,
        public \DateTimeImmutable $fetchedAtImmutable,
        public \DateTimeImmutable $capturedAtImmutable,
    ) {
    }
}

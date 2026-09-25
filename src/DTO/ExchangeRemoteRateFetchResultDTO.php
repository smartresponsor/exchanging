<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeRemoteRateFetchResultDTO
{
    /** @param array<string, mixed> $rawPayload */
    public function __construct(
        public string $baseCurrencyCode,
        public string $quoteCurrencyCode,
        public string $rateValue,
        public string $providerCode,
        public \DateTimeImmutable $rateDate,
        public \DateTimeImmutable $fetchedAtImmutable,
        public array $rawPayload = [],
    ) {
    }
}

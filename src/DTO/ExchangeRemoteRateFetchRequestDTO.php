<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeRemoteRateFetchRequestDTO
{
    public function __construct(
        public string $baseCurrencyCode,
        public string $quoteCurrencyCode,
        public string $providerCode,
        public ?\DateTimeImmutable $rateDate = null,
    ) {
    }
}

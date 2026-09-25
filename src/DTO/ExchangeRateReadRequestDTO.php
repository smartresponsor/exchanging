<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeRateReadRequestDTO
{
    public function __construct(
        public ?string $baseCurrencyCode = null,
        public ?string $quoteCurrencyCode = null,
        public ?string $providerCode = null,
        public int $limit = 20,
    ) {
    }
}

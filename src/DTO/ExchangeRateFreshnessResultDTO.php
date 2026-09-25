<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeRateFreshnessResultDTO
{
    public function __construct(
        public bool $fresh,
        public int $ageSeconds,
        public int $maxAgeSeconds,
        public string $reason,
    ) {
    }
}

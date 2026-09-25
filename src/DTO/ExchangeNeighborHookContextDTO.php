<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeNeighborHookContextDTO
{
    /**
     * @param array<string, mixed> $currency
     * @param array<string, mixed> $taxation
     * @param array<string, mixed> $billing
     * @param array<string, mixed> $paying
     * @param array<string, mixed> $ordering
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public array $currency = [],
        public array $taxation = [],
        public array $billing = [],
        public array $paying = [],
        public array $ordering = [],
        public array $metadata = [],
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeRateReadResultDTO
{
    /**
     * @param list<ExchangeRateReadItemDTO> $items
     */
    public function __construct(
        public array $items,
        public int $count,
    ) {
    }
}

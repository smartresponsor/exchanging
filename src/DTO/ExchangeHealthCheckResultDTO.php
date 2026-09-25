<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeHealthCheckResultDTO
{
    /**
     * @param list<ExchangeHealthCheckItemDTO> $items
     */
    public function __construct(
        public bool $healthy,
        public array $items,
        public \DateTimeImmutable $checkedAtImmutable,
    ) {
    }
}

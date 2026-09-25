<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeNbuRateNormalizedResponseDTO
{
    /**
     * @param array<string, mixed> $rawRow
     */
    public function __construct(
        public string $rateValue,
        public \DateTimeImmutable $rateDate,
        public array $rawRow,
    ) {
    }
}

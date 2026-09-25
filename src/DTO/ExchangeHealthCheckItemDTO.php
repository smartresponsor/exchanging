<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeHealthCheckItemDTO
{
    public function __construct(
        public string $nameEntity,
        public bool $passed,
        public string $message,
    ) {
    }
}

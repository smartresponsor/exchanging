<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeTemplateContextDTO
{
    /**
     * @param array<string, mixed> $money
     * @param array<string, mixed> $rate
     * @param array<string, mixed> $provider
     * @param array<string, mixed> $freshness
     * @param array<string, mixed> $audit
     * @param array<string, mixed> $links
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $component,
        public string $viewType,
        public string $title,
        public array $money,
        public array $rate,
        public array $provider,
        public array $freshness,
        public array $audit,
        public array $links,
        public array $metadata = [],
    ) {
    }
}

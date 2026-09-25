<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeTemplateContextRequestDTO
{
    public function __construct(
        public string $baseCurrencyCode,
        public string $quoteCurrencyCode,
        public string $amount,
        public ?string $providerCode = null,
        public ?\DateTimeImmutable $rateDate = null,
        public string $viewType = 'exchange_quote_summary',
    ) {
    }
}

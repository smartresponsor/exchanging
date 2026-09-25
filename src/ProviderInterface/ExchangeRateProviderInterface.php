<?php

declare(strict_types=1);

namespace App\Exchanging\ProviderInterface;

use App\Exchanging\DTO\ExchangeRateLookupRequestDTO;
use App\Exchanging\DTO\ExchangeRateLookupResultDTO;

interface ExchangeRateProviderInterface
{
    public function providerCode(): string;

    public function supports(ExchangeRateLookupRequestDTO $request): bool;

    public function lookup(ExchangeRateLookupRequestDTO $request): ?ExchangeRateLookupResultDTO;
}

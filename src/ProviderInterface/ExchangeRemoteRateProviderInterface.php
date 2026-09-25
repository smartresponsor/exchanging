<?php

declare(strict_types=1);

namespace App\Exchanging\ProviderInterface;

use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchResultDTO;

interface ExchangeRemoteRateProviderInterface
{
    public function providerCode(): string;

    public function supports(ExchangeRemoteRateFetchRequestDTO $request): bool;

    public function fetch(ExchangeRemoteRateFetchRequestDTO $request): ExchangeRemoteRateFetchResultDTO;
}

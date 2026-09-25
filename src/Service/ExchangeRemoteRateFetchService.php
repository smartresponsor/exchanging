<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchResultDTO;
use App\Exchanging\ServiceInterface\ExchangeRemoteRateFetchServiceInterface;

final readonly class ExchangeRemoteRateFetchService implements ExchangeRemoteRateFetchServiceInterface
{
    public function __construct(private ExchangeRemoteRateProviderRegistry $exchangeRemoteRateProviderRegistry)
    {
    }

    public function fetch(ExchangeRemoteRateFetchRequestDTO $request): ExchangeRemoteRateFetchResultDTO
    {
        return $this->exchangeRemoteRateProviderRegistry
            ->providerFor($request)
            ->fetch($request);
    }
}

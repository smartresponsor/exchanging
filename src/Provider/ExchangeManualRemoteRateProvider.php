<?php

declare(strict_types=1);

namespace App\Exchanging\Provider;

use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchResultDTO;
use App\Exchanging\ProviderInterface\ExchangeRemoteRateProviderInterface;

final readonly class ExchangeManualRemoteRateProvider implements ExchangeRemoteRateProviderInterface
{
    public function providerCode(): string
    {
        return 'manual';
    }

    public function supports(ExchangeRemoteRateFetchRequestDTO $request): bool
    {
        return strtolower($request->providerCode) === $this->providerCode();
    }

    public function fetch(ExchangeRemoteRateFetchRequestDTO $request): ExchangeRemoteRateFetchResultDTO
    {
        throw new \LogicException('Manual provider cannot fetch rates remotely. Use exchanging:rate:capture for manual rates.');
    }
}

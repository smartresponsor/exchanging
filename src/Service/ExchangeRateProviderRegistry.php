<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeRateLookupRequestDTO;
use App\Exchanging\DTO\ExchangeRateLookupResultDTO;
use App\Exchanging\ProviderInterface\ExchangeRateProviderInterface;

final readonly class ExchangeRateProviderRegistry
{
    /**
     * @param iterable<ExchangeRateProviderInterface> $providers
     */
    public function __construct(private iterable $providers)
    {
    }

    public function lookup(ExchangeRateLookupRequestDTO $request): ?ExchangeRateLookupResultDTO
    {
        foreach ($this->providers as $provider) {
            if (!$provider->supports($request)) {
                continue;
            }

            $result = $provider->lookup($request);

            if (null !== $result) {
                return $result;
            }
        }

        return null;
    }

    public function providerCount(): int
    {
        $count = 0;
        foreach ($this->providers as $provider) {
            ++$count;
        }

        return $count;
    }
}

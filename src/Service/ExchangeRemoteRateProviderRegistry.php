<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\Exception\ExchangeRemoteRateFetchException;
use App\Exchanging\ProviderInterface\ExchangeRemoteRateProviderInterface;

final readonly class ExchangeRemoteRateProviderRegistry
{
    /**
     * @param iterable<ExchangeRemoteRateProviderInterface> $providers
     */
    public function __construct(private iterable $providers)
    {
    }

    public function providerFor(ExchangeRemoteRateFetchRequestDTO $request): ExchangeRemoteRateProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($request)) {
                return $provider;
            }
        }

        throw ExchangeRemoteRateFetchException::unsupportedProvider($request->providerCode);
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

<?php

declare(strict_types=1);

namespace App\Exchanging\Provider;

use App\Exchanging\DTO\ExchangeRateLookupRequestDTO;
use App\Exchanging\DTO\ExchangeRateLookupResultDTO;
use App\Exchanging\ProviderInterface\ExchangeRateProviderInterface;
use App\Exchanging\Repository\ExchangeRateRepository;

final readonly class ExchangeRepositoryRateProvider implements ExchangeRateProviderInterface
{
    public function __construct(private ExchangeRateRepository $exchangeRateRepository)
    {
    }

    public function providerCode(): string
    {
        return 'repository';
    }

    public function supports(ExchangeRateLookupRequestDTO $request): bool
    {
        return true;
    }

    public function lookup(ExchangeRateLookupRequestDTO $request): ?ExchangeRateLookupResultDTO
    {
        $pair = $request->pair();
        $exchangeRate = null === $request->requestedForDate()
            ? $this->exchangeRateRepository->findLatestForPair($pair->baseCurrencyCode(), $pair->quoteCurrencyCode())
            : $this->exchangeRateRepository->findLatestForPairOnOrBeforeDate(
                $pair->baseCurrencyCode(),
                $pair->quoteCurrencyCode(),
                $request->requestedForDate(),
            );

        if (null === $exchangeRate) {
            return null;
        }

        return new ExchangeRateLookupResultDTO(
            pair: $pair,
            rateValue: $exchangeRate->rateValue(),
            providerCode: $exchangeRate->providerCode(),
            rateDate: $exchangeRate->rateDate(),
            capturedAtImmutable: $exchangeRate->capturedAtImmutable(),
        );
    }
}

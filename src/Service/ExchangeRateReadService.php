<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeRateReadItemDTO;
use App\Exchanging\DTO\ExchangeRateReadRequestDTO;
use App\Exchanging\DTO\ExchangeRateReadResultDTO;
use App\Exchanging\Repository\ExchangeRateRepository;
use App\Exchanging\ServiceInterface\ExchangeRateReadServiceInterface;

final readonly class ExchangeRateReadService implements ExchangeRateReadServiceInterface
{
    public function __construct(private ExchangeRateRepository $exchangeRateRepository)
    {
    }

    public function latest(ExchangeRateReadRequestDTO $request): ExchangeRateReadResultDTO
    {
        $items = [];

        foreach ($this->exchangeRateRepository->findLatestRates($request) as $rate) {
            $exchangeRateId = $rate->id();
            if (null === $exchangeRateId) {
                throw new \RuntimeException('Exchange rate ID was not generated.');
            }

            $items[] = new ExchangeRateReadItemDTO(
                $exchangeRateId,
                $rate->baseCurrencyCode(),
                $rate->quoteCurrencyCode(),
                $rate->rateValue(),
                $rate->providerCode(),
                $rate->rateDate(),
                $rate->capturedAtImmutable(),
            );
        }

        return new ExchangeRateReadResultDTO($items, count($items));
    }
}

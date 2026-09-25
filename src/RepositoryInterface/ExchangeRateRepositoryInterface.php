<?php

declare(strict_types=1);

namespace App\Exchanging\RepositoryInterface;

use App\Exchanging\DTO\ExchangeRateReadRequestDTO;
use App\Exchanging\Entity\ExchangeRateEntity;

interface ExchangeRateRepositoryInterface
{
    public function save(ExchangeRateEntity $exchangeRate, bool $flush = true): void;
    public function findLatestForPair(string $baseCurrencyCode, string $quoteCurrencyCode): ?ExchangeRateEntity;
    public function findLatestForPairOnOrBeforeDate(string $baseCurrencyCode, string $quoteCurrencyCode, \DateTimeImmutable $rateDate): ?ExchangeRateEntity;

    /** @return list<ExchangeRateEntity> */
    public function findLatestRates(ExchangeRateReadRequestDTO $request): array;
}

<?php

declare(strict_types=1);

namespace App\Exchanging\RepositoryInterface;

use App\Exchanging\Entity\ExchangeEntity;

interface ExchangeRepositoryInterface
{
    public function save(ExchangeEntity $exchange, bool $flush = true): void;
    public function findActivePair(string $baseCurrencyCode, string $quoteCurrencyCode): ?ExchangeEntity;
}

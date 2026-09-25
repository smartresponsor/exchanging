<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

use App\Exchanging\DTO\ExchangeRateFreshnessRequestDTO;
use App\Exchanging\DTO\ExchangeRateFreshnessResultDTO;

interface ExchangeRateFreshnessServiceInterface
{
    public function inspect(ExchangeRateFreshnessRequestDTO $request): ExchangeRateFreshnessResultDTO;

    public function assertFresh(ExchangeRateFreshnessRequestDTO $request): void;
}

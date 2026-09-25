<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

use App\Exchanging\DTO\ExchangeRateReadRequestDTO;
use App\Exchanging\DTO\ExchangeRateReadResultDTO;

interface ExchangeRateReadServiceInterface
{
    public function latest(ExchangeRateReadRequestDTO $request): ExchangeRateReadResultDTO;
}

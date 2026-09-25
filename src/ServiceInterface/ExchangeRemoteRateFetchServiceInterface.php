<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchResultDTO;

interface ExchangeRemoteRateFetchServiceInterface
{
    public function fetch(ExchangeRemoteRateFetchRequestDTO $request): ExchangeRemoteRateFetchResultDTO;
}

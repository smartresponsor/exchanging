<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

use App\Exchanging\DTO\ExchangeRateCaptureRequestDTO;
use App\Exchanging\DTO\ExchangeRateCaptureResultDTO;

interface ExchangeRateCaptureServiceInterface
{
    public function capture(ExchangeRateCaptureRequestDTO $request): ExchangeRateCaptureResultDTO;
}

<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

use App\Exchanging\DTO\ExchangeFetchAndCaptureRequestDTO;
use App\Exchanging\DTO\ExchangeFetchAndCaptureResultDTO;

interface ExchangeFetchAndCaptureServiceInterface
{
    public function fetchAndCapture(ExchangeFetchAndCaptureRequestDTO $request): ExchangeFetchAndCaptureResultDTO;
}

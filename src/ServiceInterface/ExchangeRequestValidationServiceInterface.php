<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

use App\Exchanging\DTO\ExchangeQuoteRequestDTO;
use App\Exchanging\DTO\ExchangeRateCaptureRequestDTO;

interface ExchangeRequestValidationServiceInterface
{
    public function validateQuoteRequest(ExchangeQuoteRequestDTO $request): ExchangeQuoteRequestDTO;

    public function validateRateCaptureRequest(ExchangeRateCaptureRequestDTO $request): ExchangeRateCaptureRequestDTO;
}

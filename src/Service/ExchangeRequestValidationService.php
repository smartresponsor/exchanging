<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeQuoteRequestDTO;
use App\Exchanging\DTO\ExchangeRateCaptureRequestDTO;
use App\Exchanging\Exception\ExchangeInvalidRequestException;
use App\Exchanging\ServiceInterface\ExchangeRequestValidationServiceInterface;
use App\Exchanging\ValueObject\ExchangeAmount;
use App\Exchanging\ValueObject\ExchangeCurrencyCode;
use App\Exchanging\ValueObject\ExchangeRateValue;

final readonly class ExchangeRequestValidationService implements ExchangeRequestValidationServiceInterface
{
    public function validateQuoteRequest(ExchangeQuoteRequestDTO $request): ExchangeQuoteRequestDTO
    {
        $pair = $request->pair();

        return new ExchangeQuoteRequestDTO(
            ExchangeCurrencyCode::fromString($pair->baseCurrencyCode())->value(),
            ExchangeCurrencyCode::fromString($pair->quoteCurrencyCode())->value(),
            ExchangeAmount::fromString($request->amount())->value(),
            $request->requestedForDate(),
        );
    }

    public function validateRateCaptureRequest(ExchangeRateCaptureRequestDTO $request): ExchangeRateCaptureRequestDTO
    {
        $violations = [];
        $baseCurrencyCode = '';
        $quoteCurrencyCode = '';
        $rateValue = '';

        try {
            $baseCurrencyCode = ExchangeCurrencyCode::fromString($request->baseCurrencyCode)->value();
        } catch (\InvalidArgumentException $exception) {
            $violations[] = 'baseCurrencyCode: ' . $exception->getMessage();
        }

        try {
            $quoteCurrencyCode = ExchangeCurrencyCode::fromString($request->quoteCurrencyCode)->value();
        } catch (\InvalidArgumentException $exception) {
            $violations[] = 'quoteCurrencyCode: ' . $exception->getMessage();
        }

        try {
            $rateValue = ExchangeRateValue::fromString($request->rateValue)->value();
        } catch (\InvalidArgumentException $exception) {
            $violations[] = 'rateValue: ' . $exception->getMessage();
        }

        $providerCode = trim($request->providerCode);
        if ($providerCode === '') {
            $violations[] = 'providerCode: Provider code is required.';
        }

        if ($violations !== []) {
            throw ExchangeInvalidRequestException::withViolations($violations);
        }

        return new ExchangeRateCaptureRequestDTO(
            $baseCurrencyCode,
            $quoteCurrencyCode,
            $rateValue,
            $providerCode,
            $request->rateDate,
        );
    }
}

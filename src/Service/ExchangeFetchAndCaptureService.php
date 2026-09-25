<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeFetchAndCaptureRequestDTO;
use App\Exchanging\DTO\ExchangeFetchAndCaptureResultDTO;
use App\Exchanging\DTO\ExchangeRateCaptureRequestDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\ServiceInterface\ExchangeFetchAndCaptureServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRateCaptureServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRemoteRateFetchServiceInterface;

final readonly class ExchangeFetchAndCaptureService implements ExchangeFetchAndCaptureServiceInterface
{
    public function __construct(
        private ExchangeRemoteRateFetchServiceInterface $exchangeRemoteRateFetchService,
        private ExchangeRateCaptureServiceInterface $exchangeRateCaptureService,
    ) {
    }

    public function fetchAndCapture(ExchangeFetchAndCaptureRequestDTO $request): ExchangeFetchAndCaptureResultDTO
    {
        $fetched = $this->exchangeRemoteRateFetchService->fetch(new ExchangeRemoteRateFetchRequestDTO(
            $request->baseCurrencyCode,
            $request->quoteCurrencyCode,
            $request->providerCode,
            $request->rateDate,
        ));

        $captured = $this->exchangeRateCaptureService->capture(new ExchangeRateCaptureRequestDTO(
            $fetched->baseCurrencyCode,
            $fetched->quoteCurrencyCode,
            $fetched->rateValue,
            $fetched->providerCode,
            $fetched->rateDate,
        ));

        return new ExchangeFetchAndCaptureResultDTO(
            $captured->exchangeRateId,
            $captured->baseCurrencyCode,
            $captured->quoteCurrencyCode,
            $captured->rateValue,
            $captured->providerCode,
            $captured->rateDate,
            $fetched->fetchedAtImmutable,
            $captured->capturedAtImmutable,
        );
    }
}

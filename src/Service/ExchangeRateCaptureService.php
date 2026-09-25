<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeRateCaptureRequestDTO;
use App\Exchanging\DTO\ExchangeRateCaptureResultDTO;
use App\Exchanging\Entity\ExchangeRateEntity;
use App\Exchanging\Repository\ExchangeRateRepository;
use App\Exchanging\ServiceInterface\ExchangeRateCaptureServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRequestValidationServiceInterface;

final readonly class ExchangeRateCaptureService implements ExchangeRateCaptureServiceInterface
{
    public function __construct(
        private ExchangeRateRepository $exchangeRateRepository,
        private ExchangeRequestValidationServiceInterface $exchangeRequestValidationService,
    ) {
    }

    public function capture(ExchangeRateCaptureRequestDTO $request): ExchangeRateCaptureResultDTO
    {
        $request = $this->exchangeRequestValidationService->validateRateCaptureRequest($request);

        $exchangeRate = new ExchangeRateEntity(
            $request->baseCurrencyCode,
            $request->quoteCurrencyCode,
            $request->rateValue,
            $request->providerCode,
            $request->rateDate ?? new \DateTimeImmutable('today'),
        );

        $this->exchangeRateRepository->save($exchangeRate);
        $exchangeRateId = $exchangeRate->id();
        if (null === $exchangeRateId) {
            throw new \RuntimeException('Exchange rate ID was not generated.');
        }

        return new ExchangeRateCaptureResultDTO(
            $exchangeRateId,
            $exchangeRate->baseCurrencyCode(),
            $exchangeRate->quoteCurrencyCode(),
            $exchangeRate->rateValue(),
            $exchangeRate->providerCode(),
            $exchangeRate->rateDate(),
            $exchangeRate->capturedAtImmutable(),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeQuoteRequestDTO;
use App\Exchanging\DTO\ExchangeQuoteResultDTO;
use App\Exchanging\DTO\ExchangeRateFreshnessRequestDTO;
use App\Exchanging\DTO\ExchangeRateLookupRequestDTO;
use App\Exchanging\Exception\ExchangeRateNotFoundException;
use App\Exchanging\ServiceInterface\ExchangeQuoteServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRateFreshnessServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRequestValidationServiceInterface;
use App\Exchanging\ValueObject\ExchangeAmount;
use App\Exchanging\ValueObject\ExchangeRateValue;
use Brick\Math\RoundingMode;

final readonly class ExchangeQuoteService implements ExchangeQuoteServiceInterface
{
    public function __construct(
        private ExchangeRateProviderRegistry $exchangeRateProviderRegistry,
        private ExchangeRequestValidationServiceInterface $exchangeRequestValidationService,
        private ExchangeRateFreshnessServiceInterface $exchangeRateFreshnessService,
        private bool $enforceRateFreshnessOnQuote = false,
    ) {
    }

    public function quote(ExchangeQuoteRequestDTO $request): ExchangeQuoteResultDTO
    {
        $request = $this->exchangeRequestValidationService->validateQuoteRequest($request);
        $pair = $request->pair();

        $rate = $this->exchangeRateProviderRegistry->lookup(new ExchangeRateLookupRequestDTO(
            $pair->baseCurrencyCode(),
            $pair->quoteCurrencyCode(),
            $request->requestedForDate(),
        ));

        if ($rate === null) {
            throw ExchangeRateNotFoundException::forPair($pair);
        }

        if ($this->enforceRateFreshnessOnQuote) {
            $this->exchangeRateFreshnessService->assertFresh(new ExchangeRateFreshnessRequestDTO(
                $rate->pair()->baseCurrencyCode(),
                $rate->pair()->quoteCurrencyCode(),
                $rate->providerCode(),
                $rate->rateDate(),
                $rate->capturedAtImmutable(),
            ));
        }

        $convertedAmount = ExchangeAmount::fromString($request->amount())
            ->multiplyBy(ExchangeRateValue::fromString($rate->rateValue()), 10, RoundingMode::Down);

        return new ExchangeQuoteResultDTO(
            $pair,
            $request->amount(),
            $rate->rateValue(),
            $convertedAmount,
            $rate->providerCode(),
            $rate->rateDate(),
            $rate->capturedAtImmutable(),
        );
    }
}

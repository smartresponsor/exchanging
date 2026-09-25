<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeOperationalStatusDTO;
use App\Exchanging\ServiceInterface\ExchangeOperationalStatusServiceInterface;

final readonly class ExchangeOperationalStatusService implements ExchangeOperationalStatusServiceInterface
{
    public function __construct(
        private int $rateFreshnessMaxAgeSeconds = 86400,
        private bool $enforceRateFreshnessOnQuote = false,
        private string $httpJsonProviderCode = 'http-json',
        private string $httpJsonEndpointUrl = '',
    ) {
    }

    public function status(): ExchangeOperationalStatusDTO
    {
        return new ExchangeOperationalStatusDTO(
            'Exchanging',
            'exchanging/exchange',
            'App\\Exchanging',
            'exchange_',
            [
                'ExchangeQuoteServiceInterface',
                'ExchangeRateCaptureServiceInterface',
                'ExchangeRateProviderInterface',
                'ExchangeRequestValidationServiceInterface',
                'ExchangeRemoteRateFetchServiceInterface',
                'ExchangeRemoteRateProviderInterface',
                'ExchangeFetchAndCaptureServiceInterface',
                'ExchangeRateFreshnessServiceInterface',
                'ExchangeAppliedRateAuditServiceInterface',
                'ExchangeDemoRateSeedServiceInterface',
                'ExchangeRateReadServiceInterface',
                'ExchangeOperationalStatusServiceInterface',
                'ExchangeHealthCheckServiceInterface',
            ],
            [
                'POST /exchanging/quote',
                'POST /exchanging/rates/capture',
                'GET /exchanging/rates/latest',
                'GET /exchanging/status',
                'GET /exchanging/health',
            ],
            [
                'exchanging:rate:capture',
                'exchanging:quote',
                'exchanging:rate:fetch',
                'exchanging:rate:fetch-capture',
                'exchanging:demo:seed-rates',
                'exchanging:rate:latest',
                'exchanging:status',
                'exchanging:health',
            ],
            [
                'rateFreshnessMaxAgeSeconds' => $this->rateFreshnessMaxAgeSeconds,
                'enforceRateFreshnessOnQuote' => $this->enforceRateFreshnessOnQuote,
                'httpJsonProviderCode' => $this->httpJsonProviderCode,
                'httpJsonEndpointConfigured' => $this->httpJsonEndpointUrl !== '',
            ],
            new \DateTimeImmutable(),
        );
    }
}

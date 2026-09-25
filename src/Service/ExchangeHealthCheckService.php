<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeHealthCheckItemDTO;
use App\Exchanging\DTO\ExchangeHealthCheckResultDTO;
use App\Exchanging\DTO\ExchangeRateFreshnessRequestDTO;
use App\Exchanging\ServiceInterface\ExchangeHealthCheckServiceInterface;

final readonly class ExchangeHealthCheckService implements ExchangeHealthCheckServiceInterface
{
    public function __construct(
        private ExchangeRateProviderRegistry $exchangeRateProviderRegistry,
        private ExchangeRemoteRateProviderRegistry $exchangeRemoteRateProviderRegistry,
        private ExchangeRateFreshnessService $exchangeRateFreshnessService,
        private ExchangeOperationalStatusService $exchangeOperationalStatusService,
    ) {
    }

    public function check(): ExchangeHealthCheckResultDTO
    {
        $now = new \DateTimeImmutable();
        $freshness = $this->exchangeRateFreshnessService->inspect(new ExchangeRateFreshnessRequestDTO(
            'USD',
            'EUR',
            'health-probe',
            $now->setTime(0, 0),
            $now,
            $now,
        ));
        $status = $this->exchangeOperationalStatusService->status();

        $items = [
            new ExchangeHealthCheckItemDTO(
                'local_rate_provider_registry',
                $this->exchangeRateProviderRegistry->providerCount() > 0,
                'At least one local rate provider is registered.',
            ),
            new ExchangeHealthCheckItemDTO(
                'remote_rate_provider_registry',
                $this->exchangeRemoteRateProviderRegistry->providerCount() > 0,
                'At least one remote rate provider is registered.',
            ),
            new ExchangeHealthCheckItemDTO(
                'freshness_policy',
                $freshness->fresh,
                'Freshness policy accepts a current capture.',
            ),
            new ExchangeHealthCheckItemDTO(
                'operational_status',
                $status->component === 'Exchanging' && $status->packageName === 'exchanging/exchange',
                'Operational status identifies the Exchanging package.',
            ),
        ];

        $healthy = true;
        foreach ($items as $item) {
            if (!$item->passed) {
                $healthy = false;
                break;
            }
        }

        return new ExchangeHealthCheckResultDTO($healthy, $items, $now);
    }
}

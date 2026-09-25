<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeDemoRateSeedDTO;
use App\Exchanging\DTO\ExchangeRateCaptureRequestDTO;
use App\Exchanging\ServiceInterface\ExchangeDemoRateSeedServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRateCaptureServiceInterface;

final readonly class ExchangeDemoRateSeedService implements ExchangeDemoRateSeedServiceInterface
{
    public function __construct(private ExchangeRateCaptureServiceInterface $exchangeRateCaptureService)
    {
    }

    public function seedDefaults(bool $flushEach = true): int
    {
        $count = 0;

        foreach ($this->defaultRates() as $rate) {
            $this->exchangeRateCaptureService->capture(new ExchangeRateCaptureRequestDTO(
                $rate->baseCurrencyCode,
                $rate->quoteCurrencyCode,
                $rate->rateValue,
                $rate->providerCode,
                $rate->rateDate,
            ));

            ++$count;
        }

        return $count;
    }

    /**
     * @return list<ExchangeDemoRateSeedDTO>
     */
    private function defaultRates(): array
    {
        $today = new \DateTimeImmutable('today');

        return [
            new ExchangeDemoRateSeedDTO('USD', 'UAH', '41.2500000000', 'demo', $today),
            new ExchangeDemoRateSeedDTO('EUR', 'USD', '1.0800000000', 'demo', $today),
            new ExchangeDemoRateSeedDTO('USD', 'EUR', '0.9259000000', 'demo', $today),
            new ExchangeDemoRateSeedDTO('GBP', 'USD', '1.2500000000', 'demo', $today),
        ];
    }
}

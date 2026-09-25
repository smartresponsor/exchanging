<?php

declare(strict_types=1);

namespace App\Exchanging\Provider;

use App\Exchanging\DTO\ExchangeRateLookupRequestDTO;
use App\Exchanging\DTO\ExchangeRateLookupResultDTO;
use App\Exchanging\ProviderInterface\ExchangeRateProviderInterface;

final readonly class ExchangeStaticRateProvider implements ExchangeRateProviderInterface
{
    /**
     * @param array<string, string> $rates keyed as BASE/QUOTE, for example USD/UAH => 41.2500000000
     */
    public function __construct(private array $rates = [])
    {
    }

    public function providerCode(): string
    {
        return 'static';
    }

    public function supports(ExchangeRateLookupRequestDTO $request): bool
    {
        return array_key_exists($this->key($request), $this->rates);
    }

    public function lookup(ExchangeRateLookupRequestDTO $request): ?ExchangeRateLookupResultDTO
    {
        $key = $this->key($request);

        if (!array_key_exists($key, $this->rates)) {
            return null;
        }

        $pair = $request->pair();
        $now = new \DateTimeImmutable();

        return new ExchangeRateLookupResultDTO(
            pair: $pair,
            rateValue: $this->rates[$key],
            providerCode: $this->providerCode(),
            rateDate: $request->requestedForDate() ?? $now,
            capturedAtImmutable: $now,
        );
    }

    private function key(ExchangeRateLookupRequestDTO $request): string
    {
        $pair = $request->pair();

        return $pair->baseCurrencyCode() . '/' . $pair->quoteCurrencyCode();
    }
}

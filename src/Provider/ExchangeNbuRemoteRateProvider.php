<?php

declare(strict_types=1);

namespace App\Exchanging\Provider;

use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchResultDTO;
use App\Exchanging\Normalizer\ExchangeNbuRateResponseNormalizer;
use App\Exchanging\ProviderInterface\ExchangeRemoteRateProviderInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ExchangeNbuRemoteRateProvider implements ExchangeRemoteRateProviderInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private ExchangeNbuRateResponseNormalizer $nbuExchangeRateResponseNormalizer,
        private string $endpointUrl = 'https://bank.gov.ua/NBUStatService/v1/statdirectory/exchange',
        private string $providerCode = 'nbu',
    ) {
    }

    public function providerCode(): string
    {
        return $this->providerCode;
    }

    public function supports(ExchangeRemoteRateFetchRequestDTO $request): bool
    {
        return strtolower($request->providerCode) === strtolower($this->providerCode)
            && strtoupper($request->quoteCurrencyCode) === 'UAH';
    }

    public function fetch(ExchangeRemoteRateFetchRequestDTO $request): ExchangeRemoteRateFetchResultDTO
    {
        $baseCurrencyCode = strtoupper(trim($request->baseCurrencyCode));
        $quoteCurrencyCode = strtoupper(trim($request->quoteCurrencyCode));

        if ($baseCurrencyCode === '') {
            throw new \InvalidArgumentException('baseCurrencyCode is required for NBU provider.');
        }

        if ($quoteCurrencyCode !== 'UAH') {
            throw new \InvalidArgumentException('NBU provider supports direct foreign-currency to UAH rates only.');
        }

        $query = [
            'valcode' => $baseCurrencyCode,
            'json' => null,
        ];

        if ($request->rateDate !== null) {
            $query['date'] = $request->rateDate->format('Ymd');
        }

        try {
            $payload = $this->httpClient
                ->request('GET', $this->endpointUrl, ['query' => $query])
                ->toArray(false);
        } catch (TransportExceptionInterface $exception) {
            throw new \RuntimeException(sprintf(
                'NBU provider transport error for %s/%s: %s',
                $baseCurrencyCode,
                $quoteCurrencyCode,
                $exception->getMessage(),
            ), previous: $exception);
        }

        $normalized = $this->nbuExchangeRateResponseNormalizer->normalize(
            $payload,
            $baseCurrencyCode,
            $request->rateDate,
        );

        return new ExchangeRemoteRateFetchResultDTO(
            $baseCurrencyCode,
            $quoteCurrencyCode,
            $normalized->rateValue,
            $this->providerCode,
            $normalized->rateDate,
            new \DateTimeImmutable(),
            $normalized->rawRow,
        );
    }
}

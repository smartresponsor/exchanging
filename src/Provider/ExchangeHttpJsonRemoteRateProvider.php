<?php

declare(strict_types=1);

namespace App\Exchanging\Provider;

use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchResultDTO;
use App\Exchanging\Exception\ExchangeRemoteRateFetchException;
use App\Exchanging\ProviderInterface\ExchangeRemoteRateProviderInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ExchangeHttpJsonRemoteRateProvider implements ExchangeRemoteRateProviderInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $providerCode = 'http-json',
        private string $endpointUrl = '',
    ) {
    }

    public function providerCode(): string
    {
        return $this->providerCode;
    }

    public function supports(ExchangeRemoteRateFetchRequestDTO $request): bool
    {
        return strtolower($request->providerCode) === strtolower($this->providerCode);
    }

    public function fetch(ExchangeRemoteRateFetchRequestDTO $request): ExchangeRemoteRateFetchResultDTO
    {
        if ($this->endpointUrl === '') {
            throw ExchangeRemoteRateFetchException::providerUnavailable($this->providerCode);
        }

        $response = $this->httpClient->request('GET', $this->endpointUrl, [
            'query' => [
                'base' => strtoupper($request->baseCurrencyCode),
                'quote' => strtoupper($request->quoteCurrencyCode),
                'date' => $request->rateDate?->format('Y-m-d'),
            ],
        ]);

        $payload = $response->toArray(false);

        $rateValue = (string) ($payload['rateValue'] ?? $payload['rate'] ?? '');
        if ($rateValue === '') {
            throw new \UnexpectedValueException('Remote provider response does not contain rateValue.');
        }

        $rateDate = isset($payload['rateDate']) && $payload['rateDate'] !== ''
            ? new \DateTimeImmutable((string) $payload['rateDate'])
            : ($request->rateDate ?? new \DateTimeImmutable('today'));

        return new ExchangeRemoteRateFetchResultDTO(
            strtoupper($request->baseCurrencyCode),
            strtoupper($request->quoteCurrencyCode),
            $rateValue,
            $this->providerCode,
            $rateDate,
            new \DateTimeImmutable(),
            $payload,
        );
    }
}

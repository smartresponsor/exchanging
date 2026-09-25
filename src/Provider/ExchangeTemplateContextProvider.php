<?php

declare(strict_types=1);

namespace App\Exchanging\Provider;

use App\Exchanging\DTO\ExchangeAppliedRateAuditRequestDTO;
use App\Exchanging\DTO\ExchangeQuoteRequestDTO;
use App\Exchanging\DTO\ExchangeTemplateContextDTO;
use App\Exchanging\DTO\ExchangeTemplateContextRequestDTO;
use App\Exchanging\ProviderInterface\ExchangeTemplateContextProviderInterface;
use App\Exchanging\ServiceInterface\ExchangeAppliedRateAuditServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeQuoteServiceInterface;

final readonly class ExchangeTemplateContextProvider implements ExchangeTemplateContextProviderInterface
{
    public function __construct(
        private ExchangeQuoteServiceInterface $exchangeQuoteService,
        private ExchangeAppliedRateAuditServiceInterface $exchangeAppliedRateAuditService,
    ) {
    }

    public function provideTemplateContext(ExchangeTemplateContextRequestDTO $request): ExchangeTemplateContextDTO
    {
        $quote = $this->exchangeQuoteService->quote(new ExchangeQuoteRequestDTO(
            $request->baseCurrencyCode,
            $request->quoteCurrencyCode,
            $request->amount,
            $request->rateDate,
        ));

        if ($request->providerCode !== null && $request->providerCode !== $quote->providerCode()) {
            throw new \InvalidArgumentException(sprintf(
                'Requested provider %s did not produce the selected quote.',
                $request->providerCode,
            ));
        }

        $pair = $quote->pair();
        $audit = $this->exchangeAppliedRateAuditService->buildAudit(new ExchangeAppliedRateAuditRequestDTO(
            $pair->baseCurrencyCode(),
            $pair->quoteCurrencyCode(),
            $quote->sourceAmount(),
            $quote->convertedAmount(),
            $quote->rateValue(),
            $quote->providerCode(),
            $quote->rateDate(),
            $quote->capturedAtImmutable(),
            'template_context',
            $request->viewType,
        ));

        return new ExchangeTemplateContextDTO(
            component: 'Exchanging',
            viewType: $request->viewType,
            title: sprintf('%s/%s exchange quote', $pair->baseCurrencyCode(), $pair->quoteCurrencyCode()),
            money: [
                'baseCurrencyCode' => $pair->baseCurrencyCode(),
                'quoteCurrencyCode' => $pair->quoteCurrencyCode(),
                'baseAmount' => $quote->sourceAmount(),
                'quoteAmount' => $quote->convertedAmount(),
            ],
            rate: [
                'rateValue' => $quote->rateValue(),
                'rateDate' => $quote->rateDate()->format('Y-m-d'),
                'capturedAtImmutable' => $quote->capturedAtImmutable()->format(DATE_ATOM),
            ],
            provider: [
                'providerCode' => $quote->providerCode(),
            ],
            freshness: [
                'status' => 'quote_returned',
                'checkedAtImmutable' => (new \DateTimeImmutable())->format(DATE_ATOM),
            ],
            audit: [
                'appliedRateFingerprint' => $audit->auditKey,
                'source' => 'exchange_quote',
            ],
            links: [
                'latestRates' => '/exchanging/rates/latest',
                'status' => '/exchanging/status',
                'health' => '/exchanging/health',
            ],
            metadata: [
                'bridgeTarget' => 'Interfacing',
                'templateSelector' => $request->viewType,
            ],
        );
    }
}

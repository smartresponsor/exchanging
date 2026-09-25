<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeAppliedRateAuditRequestDTO;
use App\Exchanging\DTO\ExchangeAppliedRateAuditResultDTO;
use App\Exchanging\ServiceInterface\ExchangeAppliedRateAuditServiceInterface;
use App\Exchanging\ValueObject\ExchangeAmount;
use App\Exchanging\ValueObject\ExchangeCurrencyCode;
use App\Exchanging\ValueObject\ExchangeRateValue;

final readonly class ExchangeAppliedRateAuditService implements ExchangeAppliedRateAuditServiceInterface
{
    public function buildAudit(ExchangeAppliedRateAuditRequestDTO $request): ExchangeAppliedRateAuditResultDTO
    {
        $baseCurrencyCode = ExchangeCurrencyCode::fromString($request->baseCurrencyCode)->value();
        $quoteCurrencyCode = ExchangeCurrencyCode::fromString($request->quoteCurrencyCode)->value();
        $sourceAmount = ExchangeAmount::fromString($request->sourceAmount)->value();
        $convertedAmount = ExchangeAmount::fromString($request->convertedAmount)->value();
        $rateValue = ExchangeRateValue::fromString($request->rateValue)->value();
        $providerCode = trim($request->providerCode);
        $consumerContext = trim($request->consumerContext);
        $consumerReference = trim($request->consumerReference);

        if ($providerCode === '') {
            throw new \InvalidArgumentException('providerCode is required.');
        }

        if ($consumerContext === '') {
            throw new \InvalidArgumentException('consumerContext is required.');
        }

        if ($consumerReference === '') {
            throw new \InvalidArgumentException('consumerReference is required.');
        }

        $auditPayload = [
            'baseCurrencyCode' => $baseCurrencyCode,
            'quoteCurrencyCode' => $quoteCurrencyCode,
            'sourceAmount' => $sourceAmount,
            'convertedAmount' => $convertedAmount,
            'rateValue' => $rateValue,
            'providerCode' => $providerCode,
            'rateDate' => $request->rateDate->format('Y-m-d'),
            'capturedAtImmutable' => $request->capturedAtImmutable->format(DATE_ATOM),
            'consumerContext' => $consumerContext,
            'consumerReference' => $consumerReference,
        ];

        return new ExchangeAppliedRateAuditResultDTO(
            hash('sha256', implode('|', $auditPayload)),
            $baseCurrencyCode,
            $quoteCurrencyCode,
            $sourceAmount,
            $convertedAmount,
            $rateValue,
            $providerCode,
            $request->rateDate,
            $request->capturedAtImmutable,
            $consumerContext,
            $consumerReference,
            $auditPayload,
        );
    }
}

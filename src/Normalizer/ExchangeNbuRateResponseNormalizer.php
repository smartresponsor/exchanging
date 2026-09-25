<?php

declare(strict_types=1);

namespace App\Exchanging\Normalizer;

use App\Exchanging\DTO\ExchangeNbuRateNormalizedResponseDTO;

final readonly class ExchangeNbuRateResponseNormalizer
{
    public function normalize(
        mixed $payload,
        string $baseCurrencyCode,
        ?\DateTimeImmutable $fallbackDate = null,
    ): ExchangeNbuRateNormalizedResponseDTO {
        $normalizedBaseCurrencyCode = strtoupper(trim($baseCurrencyCode));

        if ($normalizedBaseCurrencyCode === '') {
            throw new \InvalidArgumentException('baseCurrencyCode is required for NBU response normalization.');
        }

        $row = $this->extractFirstRow($payload, $normalizedBaseCurrencyCode);
        $rateValue = $this->extractRateValue($row, $normalizedBaseCurrencyCode);
        $rateDate = $this->extractRateDate($row, $fallbackDate);

        return new ExchangeNbuRateNormalizedResponseDTO($rateValue, $rateDate, $row);
    }

    /**
     * @param mixed $payload
     *
     * @return array<string, mixed>
     */
    private function extractFirstRow(mixed $payload, string $baseCurrencyCode): array
    {
        if (!is_array($payload)) {
            throw new \UnexpectedValueException(sprintf(
                'NBU response for %s is not an array.',
                $baseCurrencyCode,
            ));
        }

        if (!isset($payload[0]) || !is_array($payload[0])) {
            throw new \UnexpectedValueException(sprintf(
                'NBU response does not contain a rate row for %s.',
                $baseCurrencyCode,
            ));
        }

        /** @var array<string, mixed> $row */
        $row = $payload[0];

        return $row;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function extractRateValue(array $row, string $baseCurrencyCode): string
    {
        $rate = $row['rate'] ?? null;

        if (!is_int($rate) && !is_float($rate) && !is_string($rate)) {
            throw new \UnexpectedValueException(sprintf(
                'NBU response for %s does not contain a scalar rate.',
                $baseCurrencyCode,
            ));
        }

        $rateValue = trim((string) $rate);
        if ($rateValue === '' || !is_numeric($rateValue)) {
            throw new \UnexpectedValueException(sprintf(
                'NBU response for %s contains invalid rate value.',
                $baseCurrencyCode,
            ));
        }

        return $rateValue;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function extractRateDate(array $row, ?\DateTimeImmutable $fallbackDate): \DateTimeImmutable
    {
        $exchangeDate = $row['exchangedate'] ?? null;

        if (is_string($exchangeDate) && $exchangeDate !== '') {
            $rateDate = \DateTimeImmutable::createFromFormat('d.m.Y', $exchangeDate);
            if ($rateDate instanceof \DateTimeImmutable) {
                return $rateDate;
            }
        }

        return $fallbackDate ?? new \DateTimeImmutable('today');
    }
}

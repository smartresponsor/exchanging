<?php

declare(strict_types=1);

namespace App\Exchanging\Service;

use App\Exchanging\DTO\ExchangeRateFreshnessRequestDTO;
use App\Exchanging\DTO\ExchangeRateFreshnessResultDTO;
use App\Exchanging\Exception\ExchangeStaleRateException;
use App\Exchanging\ServiceInterface\ExchangeRateFreshnessServiceInterface;

final readonly class ExchangeRateFreshnessService implements ExchangeRateFreshnessServiceInterface
{
    public function __construct(private int $maxAgeSeconds = 86400)
    {
    }

    public function inspect(ExchangeRateFreshnessRequestDTO $request): ExchangeRateFreshnessResultDTO
    {
        $referenceTime = $request->referenceTime ?? new \DateTimeImmutable();
        $ageSeconds = max(0, $referenceTime->getTimestamp() - $request->capturedAtImmutable->getTimestamp());

        if ($ageSeconds > $this->maxAgeSeconds) {
            return new ExchangeRateFreshnessResultDTO(
                false,
                $ageSeconds,
                $this->maxAgeSeconds,
                sprintf('captured snapshot age %d seconds exceeds max age %d seconds', $ageSeconds, $this->maxAgeSeconds),
            );
        }

        return new ExchangeRateFreshnessResultDTO(
            true,
            $ageSeconds,
            $this->maxAgeSeconds,
            'fresh',
        );
    }

    public function assertFresh(ExchangeRateFreshnessRequestDTO $request): void
    {
        $result = $this->inspect($request);

        if (!$result->fresh) {
            throw ExchangeStaleRateException::forReason($result->reason);
        }
    }
}

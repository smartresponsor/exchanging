<?php

declare(strict_types=1);

namespace App\Exchanging\Exception;

final class ExchangeStaleRateException extends \RuntimeException
{
    public static function forReason(string $reason): self
    {
        return new self(sprintf('Exchange rate is stale: %s', $reason));
    }
}

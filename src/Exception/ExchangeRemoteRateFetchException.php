<?php

declare(strict_types=1);

namespace App\Exchanging\Exception;

final class ExchangeRemoteRateFetchException extends \RuntimeException
{
    public static function providerUnavailable(string $providerCode): self
    {
        return new self(sprintf('Exchange rate provider "%s" is unavailable.', $providerCode));
    }

    public static function unsupportedProvider(string $providerCode): self
    {
        return new self(sprintf('Exchange rate provider "%s" is not supported.', $providerCode));
    }
}

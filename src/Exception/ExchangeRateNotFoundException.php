<?php

declare(strict_types=1);

namespace App\Exchanging\Exception;

use App\Exchanging\ValueObject\ExchangeCurrencyPair;

final class ExchangeRateNotFoundException extends \RuntimeException
{
    public static function forPair(ExchangeCurrencyPair $pair): self
    {
        return new self(sprintf('Exchange rate was not found for pair %s.', $pair->key()));
    }
}

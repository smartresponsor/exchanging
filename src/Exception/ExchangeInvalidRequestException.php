<?php

declare(strict_types=1);

namespace App\Exchanging\Exception;

final class ExchangeInvalidRequestException extends \InvalidArgumentException
{
    /**
     * @param list<string> $violations
     */
    public static function withViolations(array $violations): self
    {
        return new self(implode(' ', $violations));
    }
}

<?php

declare(strict_types=1);

namespace App\Exchanging\Contract;

use App\Objecting\EntityInterface\ObjectAuditedInterface;

interface ExchangeEntityInterface extends ObjectAuditedInterface
{
    public function id(): ?int;
    public function baseCurrencyCode(): string;
    public function quoteCurrencyCode(): string;
    public function active(): bool;
    public function sourceCurrencyReference(): string;
    public function targetCurrencyReference(): string;
}

<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

interface ExchangePayingHookInterface
{
    /**
     * @return array<string, mixed>
     */
    public function payingHookContext(string $baseCurrencyCode, string $quoteCurrencyCode, string $amount): array;
}

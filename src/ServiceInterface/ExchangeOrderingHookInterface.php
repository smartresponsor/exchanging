<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

interface ExchangeOrderingHookInterface
{
    /**
     * @return array<string, mixed>
     */
    public function orderingHookContext(string $baseCurrencyCode, string $quoteCurrencyCode, string $amount): array;
}

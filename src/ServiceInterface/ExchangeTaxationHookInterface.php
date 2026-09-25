<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

interface ExchangeTaxationHookInterface
{
    /**
     * @return array<string, mixed>
     */
    public function taxationHookContext(string $baseCurrencyCode, string $quoteCurrencyCode, string $amount): array;
}

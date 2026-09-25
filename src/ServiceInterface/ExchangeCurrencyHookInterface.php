<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

interface ExchangeCurrencyHookInterface
{
    /**
     * @return array<string, mixed>
     */
    public function currencyHookContext(string $baseCurrencyCode, string $quoteCurrencyCode): array;
}

<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

interface ExchangeBillingHookInterface
{
    /**
     * @return array<string, mixed>
     */
    public function billingHookContext(string $baseCurrencyCode, string $quoteCurrencyCode, string $amount): array;
}

<?php

declare(strict_types=1);

namespace App\Exchanging\Provider;

use App\Exchanging\DTO\ExchangeNeighborHookContextDTO;
use App\Exchanging\ProviderInterface\ExchangeNeighborHookContextProviderInterface;

final readonly class ExchangeNeighborHookContextProvider implements ExchangeNeighborHookContextProviderInterface
{
    public function provideNeighborHookContext(): ExchangeNeighborHookContextDTO
    {
        return new ExchangeNeighborHookContextDTO(
            currency: [
                'component' => 'Currencing',
                'relationship' => 'currency code validation, minor units, display metadata',
                'expectedContract' => 'currency metadata/read contract',
                'status' => 'optional-neighbor',
            ],
            taxation: [
                'component' => 'Taxating',
                'relationship' => 'applied exchange rate for sales/VAT/taxable amount calculations',
                'expectedContract' => 'applied-rate audit payload or converted taxable base context',
                'status' => 'optional-neighbor',
            ],
            billing: [
                'component' => 'Billing',
                'relationship' => 'invoice/billing document display and converted totals',
                'expectedContract' => 'billing amount conversion context',
                'status' => 'optional-neighbor',
            ],
            paying: [
                'component' => 'Paying',
                'relationship' => 'payment capture/settlement conversion and provider rate attribution',
                'expectedContract' => 'payment applied-rate context',
                'status' => 'optional-neighbor',
            ],
            ordering: [
                'component' => 'Ordering',
                'relationship' => 'order totals, quote display, and checkout conversion preview',
                'expectedContract' => 'order exchange quote summary context',
                'status' => 'optional-neighbor',
            ],
            metadata: [
                'owner' => 'Exchanging',
                'direction' => 'outbound context for bridge/interfacing and neighbor components',
            ],
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Exchanging\ProviderInterface;

use App\Exchanging\DTO\ExchangeNeighborHookContextDTO;

interface ExchangeNeighborHookContextProviderInterface
{
    public function provideNeighborHookContext(): ExchangeNeighborHookContextDTO;
}

<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

use App\Exchanging\DTO\ExchangeOperationalStatusDTO;

interface ExchangeOperationalStatusServiceInterface
{
    public function status(): ExchangeOperationalStatusDTO;
}

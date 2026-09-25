<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

use App\Exchanging\DTO\ExchangeHealthCheckResultDTO;

interface ExchangeHealthCheckServiceInterface
{
    public function check(): ExchangeHealthCheckResultDTO;
}

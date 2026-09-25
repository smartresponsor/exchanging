<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

use App\Exchanging\DTO\ExchangeAppliedRateAuditRequestDTO;
use App\Exchanging\DTO\ExchangeAppliedRateAuditResultDTO;

interface ExchangeAppliedRateAuditServiceInterface
{
    public function buildAudit(ExchangeAppliedRateAuditRequestDTO $request): ExchangeAppliedRateAuditResultDTO;
}

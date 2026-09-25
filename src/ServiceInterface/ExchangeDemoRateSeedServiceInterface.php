<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

interface ExchangeDemoRateSeedServiceInterface
{
    public function seedDefaults(bool $flushEach = true): int;
}

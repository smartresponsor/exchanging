<?php

declare(strict_types=1);

namespace App\Exchanging\ProviderInterface;

use App\Exchanging\DTO\ExchangeTemplateContextDTO;
use App\Exchanging\DTO\ExchangeTemplateContextRequestDTO;

interface ExchangeTemplateContextProviderInterface
{
    public function provideTemplateContext(ExchangeTemplateContextRequestDTO $request): ExchangeTemplateContextDTO;
}

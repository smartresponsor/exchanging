<?php

declare(strict_types=1);

namespace App\Exchanging\ServiceInterface;

use App\Exchanging\DTO\ExchangeQuoteRequestDTO;
use App\Exchanging\DTO\ExchangeQuoteResultDTO;

interface ExchangeQuoteServiceInterface
{
    public function quote(ExchangeQuoteRequestDTO $request): ExchangeQuoteResultDTO;
}

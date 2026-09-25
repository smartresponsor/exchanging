<?php

declare(strict_types=1);

namespace App\Exchanging;

use App\Exchanging\DependencyInjection\ExchangingExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class ExchangingBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new ExchangingExtension();
    }
}

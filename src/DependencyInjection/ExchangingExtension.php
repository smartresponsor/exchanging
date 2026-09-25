<?php

declare(strict_types=1);

namespace App\Exchanging\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class ExchangingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(dirname(__DIR__, 2) . '/config'),
        );

        $loader->load('services/exchange_services.yaml');
        $loader->load('packages/exchange_provider.yaml');
    }

    public function getAlias(): string
    {
        return 'exchanging';
    }
}

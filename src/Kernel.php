<?php

declare(strict_types=1);

namespace App\Exchanging;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $bundles = require $this->getProjectDir() . '/config/bundles.php';

        foreach ($bundles as $class => $environments) {
            if (($environments[$this->environment] ?? false) || ($environments['all'] ?? false)) {
                /** @var class-string<BundleInterface> $class */
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        /*
         * Runtime mode intentionally loads only runtime-safe package files.
         *
         * Do not glob-load config/packages/*.yaml here: reusable bundle/host import
         * configs can contain host-oriented Doctrine options that are not safe for
         * the standalone runtime package set.
         */
        $loader->load($this->getProjectDir() . '/config/packages/framework.yaml');
        $loader->load($this->getProjectDir() . '/config/packages/exchange_doctrine_runtime.yaml');
        $loader->load($this->getProjectDir() . '/config/packages/exchange_doctrine_migrations.yaml');
        $loader->load($this->getProjectDir() . '/config/packages/exchange_runtime_provider.yaml');

        if (is_dir($this->getProjectDir() . '/config/packages/' . $this->environment)) {
            $loader->load($this->getProjectDir() . '/config/packages/' . $this->environment . '/*.yaml', 'glob');
        }

        $loader->load($this->getProjectDir() . '/config/services/exchange_runtime_services.yaml');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import($this->getProjectDir() . '/config/routes/exchange_runtime_routes.yaml');
    }
}

<?php

declare(strict_types=1);

namespace Tests;

use App\Exchanging\DataFixtures\ExchangeDemoFixtures;
use App\Exchanging\Entity\ExchangeEntity;
use App\Exchanging\Entity\ExchangeRateEntity;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;

final class ExchangeDemoFixturesContractTest extends TestCase
{
    public function testDemoFixturesPersistIntegerPrimaryKeys(): void
    {
        $entityManager = $this->entityManager();
        (new ExchangeDemoFixtures())->load($entityManager);

        $exchanges = $entityManager->createQuery('SELECT exchange FROM ' . ExchangeEntity::class . ' exchange ORDER BY exchange.id ASC')->getResult();
        $rates = $entityManager->createQuery('SELECT rate FROM ' . ExchangeRateEntity::class . ' rate ORDER BY rate.id ASC')->getResult();

        self::assertCount(4, $exchanges);
        self::assertCount(12, $rates);

        foreach ($exchanges as $exchange) {
            self::assertIsInt($exchange->id());
            self::assertGreaterThan(0, $exchange->id());
        }

        foreach ($rates as $rate) {
            self::assertIsInt($rate->id());
            self::assertGreaterThan(0, $rate->id());
        }
    }

    private function entityManager(): EntityManager
    {
        $projectDir = dirname(__DIR__);
        $config = ORMSetup::createAttributeMetadataConfig([$projectDir . '/src/Entity'], true);
        $config->enableNativeLazyObjects(true);
        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ]);

        $entityManager = new EntityManager($connection, $config);
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($entityManager->getMetadataFactory()->getAllMetadata());

        return $entityManager;
    }
}

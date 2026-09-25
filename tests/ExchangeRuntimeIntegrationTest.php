<?php

declare(strict_types=1);

namespace Tests;

use App\Exchanging\Entity\ExchangeEntity;
use App\Exchanging\Entity\ExchangeRateEntity;
use App\Exchanging\Kernel;
use App\Exchanging\Repository\ExchangeRateRepository;
use App\Exchanging\Repository\ExchangeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;

final class ExchangeRuntimeIntegrationTest extends TestCase
{
    private Kernel $kernel;
    private EntityManagerInterface $entityManager;
    private Application $application;

    protected function setUp(): void
    {
        $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';
        $_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '1';
        $_SERVER['APP_SECRET'] = $_ENV['APP_SECRET'] = 'exchange-test-secret';
        $_SERVER['DATABASE_URL'] = $_ENV['DATABASE_URL'] = 'sqlite:///:memory:';

        $this->kernel = new Kernel('test', true);
        $this->kernel->boot();

        $doctrine = $this->kernel->getContainer()->get('doctrine');
        self::assertInstanceOf(ManagerRegistry::class, $doctrine);
        $manager = $doctrine->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $manager);
        $this->entityManager = $manager;

        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        (new SchemaTool($this->entityManager))->createSchema($metadata);

        $this->application = new Application($this->kernel);
        $this->application->setAutoExit(false);
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->kernel->shutdown();
    }

    public function testStandaloneCaptureReadAndQuoteFlow(): void
    {
        $capture = new CommandTester($this->application->find('exchanging:rate:capture'));

        self::assertSame(Command::SUCCESS, $capture->execute([
            'base-currency-code' => 'usd',
            'quote-currency-code' => 'eur',
            'rate-value' => '1.2000000000',
            'provider-code' => 'manual',
            '--rate-date' => '2026-09-19',
        ]));
        self::assertSame(Command::SUCCESS, $capture->execute([
            'base-currency-code' => 'usd',
            'quote-currency-code' => 'eur',
            'rate-value' => '1.2500000000',
            'provider-code' => 'manual',
            '--rate-date' => '2026-09-20',
        ]));

        $latest = new CommandTester($this->application->find('exchanging:rate:latest'));
        self::assertSame(Command::SUCCESS, $latest->execute([
            '--base' => 'usd',
            '--quote' => 'eur',
            '--provider' => 'manual',
            '--limit' => '10',
        ]));
        self::assertStringContainsString('1.2500000000', $latest->getDisplay());

        $quote = new CommandTester($this->application->find('exchanging:quote'));
        self::assertSame(Command::SUCCESS, $quote->execute([
            'base-currency-code' => 'USD',
            'quote-currency-code' => 'EUR',
            'amount' => '10',
        ]));
        self::assertStringContainsString('12.5000000000', $quote->getDisplay());

        self::assertSame(Command::SUCCESS, $quote->execute([
            'base-currency-code' => 'USD',
            'quote-currency-code' => 'EUR',
            'amount' => '10',
            '--rate-date' => '2026-09-19',
        ]));
        self::assertStringContainsString('12.0000000000', $quote->getDisplay());
    }

    public function testHttpCaptureLatestQuoteStatusAndHealthFlow(): void
    {
        $capture = $this->handle(Request::create(
            '/exchanging/rates/capture',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'baseCurrencyCode' => 'USD',
                'quoteCurrencyCode' => 'UAH',
                'rateValue' => '41.2500000000',
                'providerCode' => 'manual',
                'rateDate' => '2026-09-20',
            ], JSON_THROW_ON_ERROR),
        ));
        self::assertSame(201, $capture->getStatusCode());

        $latest = $this->handle(Request::create('/exchanging/rates/latest?base=USD&quote=UAH&provider=manual&limit=10'));
        self::assertSame(200, $latest->getStatusCode());
        self::assertStringContainsString('"count":1', (string) $latest->getContent());

        $quote = $this->handle(Request::create(
            '/exchanging/quote',
            'POST',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode([
                'baseCurrencyCode' => 'USD',
                'quoteCurrencyCode' => 'UAH',
                'amount' => '2',
            ], JSON_THROW_ON_ERROR),
        ));
        self::assertSame(200, $quote->getStatusCode());
        self::assertStringContainsString('"convertedAmount":"82.5000000000"', (string) $quote->getContent());

        self::assertSame(200, $this->handle(Request::create('/exchanging/status'))->getStatusCode());
        self::assertSame(200, $this->handle(Request::create('/exchanging/health'))->getStatusCode());
    }

    public function testRepositoryAndExchangeLifecycleBehavior(): void
    {
        $exchange = new ExchangeEntity('usd', 'eur');
        self::assertTrue($exchange->active());
        self::assertSame('USD', $exchange->baseCurrencyCode());
        self::assertSame('EUR', $exchange->quoteCurrencyCode());
        self::assertSame('USD', $exchange->sourceCurrencyReference());
        self::assertSame('EUR', $exchange->targetCurrencyReference());

        $exchange->deactivate();
        self::assertFalse($exchange->active());
        $exchange->deactivate();
        self::assertFalse($exchange->active());
        $exchange->activate();
        self::assertTrue($exchange->active());
        $exchange->activate();
        self::assertTrue($exchange->active());

        $repository = $this->entityManager->getRepository(ExchangeEntity::class);
        self::assertInstanceOf(ExchangeRepository::class, $repository);
        $repository->save($exchange);
        self::assertNotNull($exchange->id());
        self::assertSame($exchange->id(), $repository->findActivePair('usd', 'eur')?->id());

        $exchange->deactivate();
        $repository->save($exchange);
        self::assertNull($repository->findActivePair('USD', 'EUR'));

        $rateRepository = $this->entityManager->getRepository(ExchangeRateEntity::class);
        self::assertInstanceOf(ExchangeRateRepository::class, $rateRepository);

        $rate = new ExchangeRateEntity('usd', 'eur', '1.25', 'manual', new \DateTimeImmutable('2026-09-20'));
        $rateRepository->save($rate);
        self::assertNotNull($rate->id());
        self::assertSame('USD', $rate->getSourceCurrency());
        self::assertSame('EUR', $rate->getTargetCurrency());
        self::assertSame('1.25', $rate->getRatio());
    }

    private function handle(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $response = $this->kernel->handle($request);
        $this->kernel->terminate($request, $response);

        return $response;
    }
}

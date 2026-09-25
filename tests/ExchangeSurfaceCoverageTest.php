<?php

declare(strict_types=1);

namespace Tests;

use App\Exchanging\Command\ExchangeDemoRateSeedCommand;
use App\Exchanging\Command\ExchangeFetchAndCaptureCommand;
use App\Exchanging\Command\ExchangeHealthCheckCommand;
use App\Exchanging\Command\ExchangeOperationalStatusCommand;
use App\Exchanging\Command\ExchangeQuoteCommand;
use App\Exchanging\Command\ExchangeRateCaptureCommand;
use App\Exchanging\Command\ExchangeRateLatestCommand;
use App\Exchanging\Command\ExchangeRemoteRateFetchCommand;
use App\Exchanging\Controller\ExchangeHealthCheckController;
use App\Exchanging\Controller\ExchangeOperationalStatusController;
use App\Exchanging\Controller\ExchangeQuoteController;
use App\Exchanging\Controller\ExchangeRateCaptureController;
use App\Exchanging\Controller\ExchangeRateLatestController;
use App\Exchanging\DTO\ExchangeFetchAndCaptureResultDTO;
use App\Exchanging\DTO\ExchangeHealthCheckItemDTO;
use App\Exchanging\DTO\ExchangeHealthCheckResultDTO;
use App\Exchanging\DTO\ExchangeOperationalStatusDTO;
use App\Exchanging\DTO\ExchangeQuoteResultDTO;
use App\Exchanging\DTO\ExchangeRateCaptureResultDTO;
use App\Exchanging\DTO\ExchangeRateReadItemDTO;
use App\Exchanging\DTO\ExchangeRateReadResultDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchResultDTO;
use App\Exchanging\ServiceInterface\ExchangeDemoRateSeedServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeFetchAndCaptureServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeHealthCheckServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeOperationalStatusServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeQuoteServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRateCaptureServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRateReadServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRemoteRateFetchServiceInterface;
use App\Exchanging\ValueObject\ExchangeCurrencyPair;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;

final class ExchangeSurfaceCoverageTest extends TestCase
{
    private \DateTimeImmutable $rateDate;
    private \DateTimeImmutable $capturedAt;

    protected function setUp(): void
    {
        $this->rateDate = new \DateTimeImmutable('2026-09-20');
        $this->capturedAt = new \DateTimeImmutable('2026-09-20T12:00:00+00:00');
    }

    public function testQuoteControllerSuccessAndBadPayload(): void
    {
        $service = $this->createStub(ExchangeQuoteServiceInterface::class);
        $service->method('quote')->willReturn($this->quoteResult());
        $controller = new ExchangeQuoteController($service);

        $response = $controller(Request::create('/exchanging/quote', 'POST', server: [
            'CONTENT_TYPE' => 'application/json',
        ], content: json_encode([
            'baseCurrencyCode' => 'USD',
            'quoteCurrencyCode' => 'EUR',
            'amount' => '10',
            'rateDate' => '2026-09-20',
        ], JSON_THROW_ON_ERROR)));

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('"convertedAmount":"12.5000000000"', (string) $response->getContent());

        $bad = $controller(Request::create('/exchanging/quote', 'POST', content: 'null'));
        self::assertSame(400, $bad->getStatusCode());
    }

    public function testRateCaptureControllerSuccessAndValidationFailure(): void
    {
        $service = $this->createStub(ExchangeRateCaptureServiceInterface::class);
        $service->method('capture')->willReturn($this->captureResult());
        $controller = new ExchangeRateCaptureController($service);

        $response = $controller(Request::create('/exchanging/rates/capture', 'POST', content: json_encode([
            'baseCurrencyCode' => 'USD',
            'quoteCurrencyCode' => 'EUR',
            'rateValue' => '1.25',
            'providerCode' => 'manual',
            'rateDate' => '2026-09-20',
        ], JSON_THROW_ON_ERROR)));

        self::assertSame(201, $response->getStatusCode());
        self::assertStringContainsString('"exchangeRateId":42', (string) $response->getContent());

        $bad = $controller(Request::create('/exchanging/rates/capture', 'POST', content: 'false'));
        self::assertSame(400, $bad->getStatusCode());
    }

    public function testLatestStatusAndHealthControllers(): void
    {
        $read = $this->createStub(ExchangeRateReadServiceInterface::class);
        $read->method('latest')->willReturn($this->readResult());
        $latest = new ExchangeRateLatestController($read);

        $latestResponse = $latest(Request::create('/exchanging/rates/latest?base=USD&quote=EUR&provider=manual&limit=1'));
        self::assertSame(200, $latestResponse->getStatusCode());
        self::assertStringContainsString('"count":1', (string) $latestResponse->getContent());

        $statusService = $this->createStub(ExchangeOperationalStatusServiceInterface::class);
        $statusService->method('status')->willReturn($this->statusResult());
        $statusResponse = (new ExchangeOperationalStatusController($statusService))();
        self::assertSame(200, $statusResponse->getStatusCode());
        self::assertStringContainsString('"component":"Exchanging"', (string) $statusResponse->getContent());

        $healthService = $this->createStub(ExchangeHealthCheckServiceInterface::class);
        $healthService->method('check')->willReturn($this->healthResult(true));
        $healthResponse = (new ExchangeHealthCheckController($healthService))();
        self::assertSame(200, $healthResponse->getStatusCode());
        self::assertStringContainsString('"healthy":true', (string) $healthResponse->getContent());

        $healthService = $this->createStub(ExchangeHealthCheckServiceInterface::class);
        $healthService->method('check')->willReturn($this->healthResult(false));
        self::assertSame(503, (new ExchangeHealthCheckController($healthService))()->getStatusCode());
    }

    public function testQuoteAndCaptureCommandsCoverSuccessAndFailure(): void
    {
        $quoteService = $this->createStub(ExchangeQuoteServiceInterface::class);
        $quoteService->method('quote')->willReturn($this->quoteResult());
        $tester = new CommandTester(new ExchangeQuoteCommand($quoteService));
        self::assertSame(Command::SUCCESS, $tester->execute([
            'base-currency-code' => 'USD',
            'quote-currency-code' => 'EUR',
            'amount' => '10',
            '--rate-date' => '2026-09-20',
        ]));
        self::assertStringContainsString('Exchange quote calculated', $tester->getDisplay());

        $quoteFailure = $this->createStub(ExchangeQuoteServiceInterface::class);
        $quoteFailure->method('quote')->willThrowException(new \RuntimeException('quote failed'));
        $tester = new CommandTester(new ExchangeQuoteCommand($quoteFailure));
        self::assertSame(Command::FAILURE, $tester->execute([
            'base-currency-code' => 'USD',
            'quote-currency-code' => 'EUR',
            'amount' => '10',
        ]));

        $captureService = $this->createStub(ExchangeRateCaptureServiceInterface::class);
        $captureService->method('capture')->willReturn($this->captureResult());
        $tester = new CommandTester(new ExchangeRateCaptureCommand($captureService));
        self::assertSame(Command::SUCCESS, $tester->execute([
            'base-currency-code' => 'USD',
            'quote-currency-code' => 'EUR',
            'rate-value' => '1.25',
            'provider-code' => 'manual',
            '--rate-date' => '2026-09-20',
        ]));

        $captureFailure = $this->createStub(ExchangeRateCaptureServiceInterface::class);
        $captureFailure->method('capture')->willThrowException(new \RuntimeException('capture failed'));
        $tester = new CommandTester(new ExchangeRateCaptureCommand($captureFailure));
        self::assertSame(Command::FAILURE, $tester->execute([
            'base-currency-code' => 'USD',
            'quote-currency-code' => 'EUR',
            'rate-value' => '1.25',
            'provider-code' => 'manual',
        ]));
    }

    public function testFetchAndFetchCaptureCommandsCoverSuccessAndFailure(): void
    {
        $fetch = $this->createStub(ExchangeRemoteRateFetchServiceInterface::class);
        $fetch->method('fetch')->willReturn($this->fetchResult());
        $tester = new CommandTester(new ExchangeRemoteRateFetchCommand($fetch));
        self::assertSame(Command::SUCCESS, $tester->execute([
            'base-currency-code' => 'USD',
            'quote-currency-code' => 'EUR',
            'provider-code' => 'remote',
            '--rate-date' => '2026-09-20',
        ]));

        $fetchFailure = $this->createStub(ExchangeRemoteRateFetchServiceInterface::class);
        $fetchFailure->method('fetch')->willThrowException(new \RuntimeException('fetch failed'));
        $tester = new CommandTester(new ExchangeRemoteRateFetchCommand($fetchFailure));
        self::assertSame(Command::FAILURE, $tester->execute([
            'base-currency-code' => 'USD',
            'quote-currency-code' => 'EUR',
            'provider-code' => 'remote',
        ]));

        $fetchCapture = $this->createStub(ExchangeFetchAndCaptureServiceInterface::class);
        $fetchCapture->method('fetchAndCapture')->willReturn($this->fetchCaptureResult());
        $tester = new CommandTester(new ExchangeFetchAndCaptureCommand($fetchCapture));
        self::assertSame(Command::SUCCESS, $tester->execute([
            'base-currency-code' => 'USD',
            'quote-currency-code' => 'EUR',
            'provider-code' => 'remote',
            '--rate-date' => '2026-09-20',
        ]));

        $failure = $this->createStub(ExchangeFetchAndCaptureServiceInterface::class);
        $failure->method('fetchAndCapture')->willThrowException(new \RuntimeException('fetch capture failed'));
        $tester = new CommandTester(new ExchangeFetchAndCaptureCommand($failure));
        self::assertSame(Command::FAILURE, $tester->execute([
            'base-currency-code' => 'USD',
            'quote-currency-code' => 'EUR',
            'provider-code' => 'remote',
        ]));
    }

    public function testSeedLatestStatusAndHealthCommands(): void
    {
        $seed = $this->createStub(ExchangeDemoRateSeedServiceInterface::class);
        $seed->method('seedDefaults')->willReturn(3);
        $tester = new CommandTester(new ExchangeDemoRateSeedCommand($seed));
        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('Seeded 3 demo exchange rates', $tester->getDisplay());

        $seedFailure = $this->createStub(ExchangeDemoRateSeedServiceInterface::class);
        $seedFailure->method('seedDefaults')->willThrowException(new \RuntimeException('seed failed'));
        self::assertSame(Command::FAILURE, (new CommandTester(new ExchangeDemoRateSeedCommand($seedFailure)))->execute([]));

        $read = $this->createStub(ExchangeRateReadServiceInterface::class);
        $read->method('latest')->willReturn($this->readResult());
        $tester = new CommandTester(new ExchangeRateLatestCommand($read));
        self::assertSame(Command::SUCCESS, $tester->execute([
            '--base' => 'USD',
            '--quote' => 'EUR',
            '--provider' => 'manual',
            '--limit' => '1',
        ]));
        self::assertStringContainsString('Latest exchange rates: 1', $tester->getDisplay());

        $status = $this->createStub(ExchangeOperationalStatusServiceInterface::class);
        $status->method('status')->willReturn($this->statusResult());
        $tester = new CommandTester(new ExchangeOperationalStatusCommand($status));
        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertStringContainsString('Exchanging operational status', $tester->getDisplay());

        $health = $this->createStub(ExchangeHealthCheckServiceInterface::class);
        $health->method('check')->willReturn($this->healthResult(true));
        self::assertSame(Command::SUCCESS, (new CommandTester(new ExchangeHealthCheckCommand($health)))->execute([]));

        $unhealthy = $this->createStub(ExchangeHealthCheckServiceInterface::class);
        $unhealthy->method('check')->willReturn($this->healthResult(false));
        self::assertSame(Command::FAILURE, (new CommandTester(new ExchangeHealthCheckCommand($unhealthy)))->execute([]));
    }

    private function quoteResult(): ExchangeQuoteResultDTO
    {
        return new ExchangeQuoteResultDTO(
            ExchangeCurrencyPair::fromStrings('USD', 'EUR'),
            '10',
            '1.25',
            '12.5000000000',
            'manual',
            $this->rateDate,
            $this->capturedAt,
        );
    }

    private function captureResult(): ExchangeRateCaptureResultDTO
    {
        return new ExchangeRateCaptureResultDTO(
            42,
            'USD',
            'EUR',
            '1.25',
            'manual',
            $this->rateDate,
            $this->capturedAt,
        );
    }

    private function readResult(): ExchangeRateReadResultDTO
    {
        return new ExchangeRateReadResultDTO([
            new ExchangeRateReadItemDTO(42, 'USD', 'EUR', '1.25', 'manual', $this->rateDate, $this->capturedAt),
        ], 1);
    }

    private function fetchResult(): ExchangeRemoteRateFetchResultDTO
    {
        return new ExchangeRemoteRateFetchResultDTO(
            'USD',
            'EUR',
            '1.25',
            'remote',
            $this->rateDate,
            $this->capturedAt,
        );
    }

    private function fetchCaptureResult(): ExchangeFetchAndCaptureResultDTO
    {
        return new ExchangeFetchAndCaptureResultDTO(
            42,
            'USD',
            'EUR',
            '1.25',
            'remote',
            $this->rateDate,
            $this->capturedAt,
            $this->capturedAt,
        );
    }

    private function statusResult(): ExchangeOperationalStatusDTO
    {
        return new ExchangeOperationalStatusDTO(
            'Exchanging',
            'exchanging/exchange',
            'App\\Exchanging',
            'exchange_',
            ['ExchangeQuoteServiceInterface'],
            ['POST /exchanging/quote'],
            ['exchanging:quote'],
            ['enabled' => true],
            $this->capturedAt,
        );
    }

    private function healthResult(bool $healthy): ExchangeHealthCheckResultDTO
    {
        return new ExchangeHealthCheckResultDTO(
            $healthy,
            [new ExchangeHealthCheckItemDTO('probe', $healthy, $healthy ? 'ok' : 'failed')],
            $this->capturedAt,
        );
    }
}

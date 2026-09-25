<?php

declare(strict_types=1);

namespace Tests;

use App\Exchanging\DTO\ExchangeFetchAndCaptureRequestDTO;
use App\Exchanging\DTO\ExchangeQuoteResultDTO;
use App\Exchanging\DTO\ExchangeRateCaptureResultDTO;
use App\Exchanging\DTO\ExchangeRateLookupRequestDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchResultDTO;
use App\Exchanging\DTO\ExchangeTemplateContextRequestDTO;
use App\Exchanging\Exception\ExchangeRemoteRateFetchException;
use App\Exchanging\Normalizer\ExchangeNbuRateResponseNormalizer;
use App\Exchanging\Policy\ExchangeRateLifecyclePolicy;
use App\Exchanging\Provider\ExchangeHttpJsonRemoteRateProvider;
use App\Exchanging\Provider\ExchangeManualRemoteRateProvider;
use App\Exchanging\Provider\ExchangeNbuRemoteRateProvider;
use App\Exchanging\Provider\ExchangeNeighborHookContextProvider;
use App\Exchanging\Provider\ExchangeStaticRateProvider;
use App\Exchanging\Provider\ExchangeTemplateContextProvider;
use App\Exchanging\ProviderInterface\ExchangeRemoteRateProviderInterface;
use App\Exchanging\Service\ExchangeAppliedRateAuditService;
use App\Exchanging\Service\ExchangeDemoRateSeedService;
use App\Exchanging\Service\ExchangeFetchAndCaptureService;
use App\Exchanging\Service\ExchangeRemoteRateFetchService;
use App\Exchanging\Service\ExchangeRemoteRateProviderRegistry;
use App\Exchanging\ServiceInterface\ExchangeQuoteServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRateCaptureServiceInterface;
use App\Exchanging\ServiceInterface\ExchangeRemoteRateFetchServiceInterface;
use App\Exchanging\ValueObject\ExchangeCurrencyPair;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ExchangeProviderAndServiceCoverageTest extends TestCase
{
    public function testStaticProviderCoversHitMissAndRequestedDate(): void
    {
        $provider = new ExchangeStaticRateProvider(['USD/EUR' => '1.2500000000']);
        $hit = new ExchangeRateLookupRequestDTO('usd', 'eur', new \DateTimeImmutable('2026-09-20'));
        $miss = new ExchangeRateLookupRequestDTO('USD', 'UAH');

        self::assertSame('static', $provider->providerCode());
        self::assertTrue($provider->supports($hit));
        self::assertFalse($provider->supports($miss));
        self::assertNull($provider->lookup($miss));

        $result = $provider->lookup($hit);
        self::assertNotNull($result);
        self::assertSame('1.2500000000', $result->rateValue());
        self::assertSame('static', $result->providerCode());
        self::assertSame('2026-09-20', $result->rateDate()->format('Y-m-d'));
        self::assertSame('USD', $result->pair()->baseCurrencyCode());
        self::assertInstanceOf(\DateTimeImmutable::class, $result->capturedAtImmutable());
    }

    public function testHttpJsonProviderCoversAvailabilitySuccessAndPayloadFailure(): void
    {
        $request = new ExchangeRemoteRateFetchRequestDTO('usd', 'eur', 'HTTP-JSON', new \DateTimeImmutable('2026-09-20'));

        $unconfigured = new ExchangeHttpJsonRemoteRateProvider(new MockHttpClient(), 'http-json', '');
        self::assertSame('http-json', $unconfigured->providerCode());
        self::assertTrue($unconfigured->supports($request));

        try {
            $unconfigured->fetch($request);
            self::fail('Unconfigured provider must fail.');
        } catch (ExchangeRemoteRateFetchException $exception) {
            self::assertStringContainsString('unavailable', $exception->getMessage());
        }

        $client = new MockHttpClient(new MockResponse(json_encode([
            'rateValue' => '1.26',
            'rateDate' => '2026-09-19',
        ], JSON_THROW_ON_ERROR), ['http_code' => 200, 'response_headers' => ['content-type: application/json']]));
        $provider = new ExchangeHttpJsonRemoteRateProvider($client, 'http-json', 'https://example.test/rate');
        $result = $provider->fetch($request);

        self::assertSame('USD', $result->baseCurrencyCode);
        self::assertSame('EUR', $result->quoteCurrencyCode);
        self::assertSame('1.26', $result->rateValue);
        self::assertSame('2026-09-19', $result->rateDate->format('Y-m-d'));

        $bad = new ExchangeHttpJsonRemoteRateProvider(
            new MockHttpClient(new MockResponse('{}', ['response_headers' => ['content-type: application/json']])),
            'http-json',
            'https://example.test/rate',
        );
        $this->expectException(\UnexpectedValueException::class);
        $bad->fetch($request);
    }

    public function testNbuNormalizerCoversValidFallbackAndInvalidPayloads(): void
    {
        $normalizer = new ExchangeNbuRateResponseNormalizer();
        $valid = $normalizer->normalize([['rate' => 41.25, 'exchangedate' => '20.09.2026']], ' usd ');
        self::assertSame('41.25', $valid->rateValue);
        self::assertSame('2026-09-20', $valid->rateDate->format('Y-m-d'));
        self::assertSame(41.25, $valid->rawRow['rate']);

        $fallback = new \DateTimeImmutable('2026-09-18');
        $fallbackResult = $normalizer->normalize([['rate' => '41.30', 'exchangedate' => 'bad-date']], 'USD', $fallback);
        self::assertSame($fallback, $fallbackResult->rateDate);

        foreach ([
            [null, 'USD', 'not an array'],
            [[], 'USD', 'does not contain a rate row'],
            [[['rate' => []]], 'USD', 'does not contain a scalar rate'],
            [[['rate' => 'abc']], 'USD', 'contains invalid rate value'],
        ] as [$payload, $currency, $message]) {
            try {
                $normalizer->normalize($payload, $currency);
                self::fail('Invalid NBU payload must fail.');
            } catch (\UnexpectedValueException $exception) {
                self::assertStringContainsString($message, $exception->getMessage());
            }
        }

        $this->expectException(\InvalidArgumentException::class);
        $normalizer->normalize([], ' ');
    }

    public function testNbuProviderSuccessSupportAndInputGuards(): void
    {
        $client = new MockHttpClient(new MockResponse(json_encode([
            ['rate' => 41.25, 'exchangedate' => '20.09.2026'],
        ], JSON_THROW_ON_ERROR), ['response_headers' => ['content-type: application/json']]));

        $provider = new ExchangeNbuRemoteRateProvider($client, new ExchangeNbuRateResponseNormalizer());
        $request = new ExchangeRemoteRateFetchRequestDTO('usd', 'uah', 'NBU', new \DateTimeImmutable('2026-09-20'));

        self::assertSame('nbu', $provider->providerCode());
        self::assertTrue($provider->supports($request));
        self::assertFalse($provider->supports(new ExchangeRemoteRateFetchRequestDTO('USD', 'EUR', 'nbu')));

        $result = $provider->fetch($request);
        self::assertSame('USD', $result->baseCurrencyCode);
        self::assertSame('UAH', $result->quoteCurrencyCode);
        self::assertSame('41.25', $result->rateValue);

        try {
            $provider->fetch(new ExchangeRemoteRateFetchRequestDTO('', 'UAH', 'nbu'));
            self::fail('Empty base currency must fail.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('baseCurrencyCode', $exception->getMessage());
        }

        $this->expectException(\InvalidArgumentException::class);
        $provider->fetch(new ExchangeRemoteRateFetchRequestDTO('USD', 'EUR', 'nbu'));
    }

    public function testManualAndRemoteFetchServices(): void
    {
        $manual = new ExchangeManualRemoteRateProvider();
        $manualRequest = new ExchangeRemoteRateFetchRequestDTO('USD', 'EUR', 'MANUAL');
        self::assertSame('manual', $manual->providerCode());
        self::assertTrue($manual->supports($manualRequest));
        self::assertFalse($manual->supports(new ExchangeRemoteRateFetchRequestDTO('USD', 'EUR', 'other')));

        try {
            $manual->fetch($manualRequest);
            self::fail('Manual provider is not remotely fetchable.');
        } catch (\LogicException $exception) {
            self::assertStringContainsString('cannot fetch', $exception->getMessage());
        }

        $provider = new class () implements ExchangeRemoteRateProviderInterface {
            public function providerCode(): string
            {
                return 'test';
            }

            public function supports(ExchangeRemoteRateFetchRequestDTO $request): bool
            {
                return $request->providerCode === 'test';
            }

            public function fetch(ExchangeRemoteRateFetchRequestDTO $request): ExchangeRemoteRateFetchResultDTO
            {
                return new ExchangeRemoteRateFetchResultDTO(
                    'USD',
                    'EUR',
                    '1.25',
                    'test',
                    new \DateTimeImmutable('2026-09-20'),
                    new \DateTimeImmutable('2026-09-20T12:00:00+00:00'),
                );
            }
        };

        $service = new ExchangeRemoteRateFetchService(new ExchangeRemoteRateProviderRegistry([$provider]));
        self::assertSame('1.25', $service->fetch(new ExchangeRemoteRateFetchRequestDTO('USD', 'EUR', 'test'))->rateValue);
    }

    public function testFetchCaptureDemoSeedTemplateAndNeighborOrchestration(): void
    {
        $fetchedAt = new \DateTimeImmutable('2026-09-20T11:59:00+00:00');
        $capturedAt = new \DateTimeImmutable('2026-09-20T12:00:00+00:00');
        $rateDate = new \DateTimeImmutable('2026-09-20');

        $fetch = $this->createStub(ExchangeRemoteRateFetchServiceInterface::class);
        $fetch->method('fetch')->willReturn(new ExchangeRemoteRateFetchResultDTO(
            'USD',
            'EUR',
            '1.25',
            'remote',
            $rateDate,
            $fetchedAt,
        ));
        $capture = $this->createStub(ExchangeRateCaptureServiceInterface::class);
        $capture->method('capture')->willReturn(new ExchangeRateCaptureResultDTO(
            7,
            'USD',
            'EUR',
            '1.25',
            'remote',
            $rateDate,
            $capturedAt,
        ));

        $orchestrator = new ExchangeFetchAndCaptureService($fetch, $capture);
        $combined = $orchestrator->fetchAndCapture(new ExchangeFetchAndCaptureRequestDTO(
            'USD',
            'EUR',
            'remote',
            $rateDate,
        ));
        self::assertSame(7, $combined->exchangeRateId);
        self::assertSame($fetchedAt, $combined->fetchedAtImmutable);
        self::assertSame($capturedAt, $combined->capturedAtImmutable);

        $seedCapture = $this->createMock(ExchangeRateCaptureServiceInterface::class);
        $seedCapture->expects(self::exactly(4))
            ->method('capture')
            ->willReturn(new ExchangeRateCaptureResultDTO(
                1,
                'USD',
                'UAH',
                '41.25',
                'demo',
                $rateDate,
                $capturedAt,
            ));
        self::assertSame(4, (new ExchangeDemoRateSeedService($seedCapture))->seedDefaults());

        $quoteService = $this->createStub(ExchangeQuoteServiceInterface::class);
        $quoteService->method('quote')->willReturn(new ExchangeQuoteResultDTO(
            ExchangeCurrencyPair::fromStrings('USD', 'EUR'),
            '10',
            '1.25',
            '12.5000000000',
            'provider-a',
            $rateDate,
            $capturedAt,
        ));
        $templateProvider = new ExchangeTemplateContextProvider($quoteService, new ExchangeAppliedRateAuditService());
        $context = $templateProvider->provideTemplateContext(new ExchangeTemplateContextRequestDTO(
            'USD',
            'EUR',
            '10',
            'provider-a',
            $rateDate,
            'summary',
        ));

        self::assertSame('Exchanging', $context->component);
        self::assertSame('USD/EUR exchange quote', $context->title);
        self::assertSame('provider-a', $context->provider['providerCode']);
        self::assertSame('Interfacing', $context->metadata['bridgeTarget']);

        try {
            $templateProvider->provideTemplateContext(new ExchangeTemplateContextRequestDTO(
                'USD',
                'EUR',
                '10',
                'different',
                $rateDate,
                'summary',
            ));
            self::fail('Provider mismatch must fail.');
        } catch (\InvalidArgumentException $exception) {
            self::assertStringContainsString('did not produce', $exception->getMessage());
        }

        $neighbors = (new ExchangeNeighborHookContextProvider())->provideNeighborHookContext();
        self::assertSame('Currencing', $neighbors->currency['component']);
        self::assertSame('Taxating', $neighbors->taxation['component']);
        self::assertSame('Billing', $neighbors->billing['component']);
        self::assertSame('Paying', $neighbors->paying['component']);
        self::assertSame('Ordering', $neighbors->ordering['component']);
    }

    public function testLifecyclePolicyCoversAllowedSameUnknownAndRejectedTransitions(): void
    {
        $policy = new ExchangeRateLifecyclePolicy();

        self::assertTrue($policy->canTransition(' DRAFT ', 'published'));
        self::assertTrue($policy->canTransition('published', 'PUBLISHED'));
        self::assertFalse($policy->canTransition('published', 'draft'));
        self::assertFalse($policy->canTransition('unknown', 'published'));
        self::assertSame(['published', 'rejected'], $policy->allowedNextStatuses('DRAFT'));
        self::assertSame([], $policy->allowedNextStatuses('unknown'));
        $policy->assertCanTransition('rejected', 'draft');

        $this->expectException(\DomainException::class);
        $policy->assertCanTransition('archived', 'published');
    }
}

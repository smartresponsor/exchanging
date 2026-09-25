<?php

declare(strict_types=1);

namespace Tests;

use App\Exchanging\DTO\ExchangeAppliedRateAuditRequestDTO;
use App\Exchanging\DTO\ExchangeRateCaptureRequestDTO;
use App\Exchanging\DTO\ExchangeRateFreshnessRequestDTO;
use App\Exchanging\DTO\ExchangeRateLookupRequestDTO;
use App\Exchanging\DTO\ExchangeRateLookupResultDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\DTO\ExchangeRemoteRateFetchResultDTO;
use App\Exchanging\Exception\ExchangeInvalidRequestException;
use App\Exchanging\Exception\ExchangeRemoteRateFetchException;
use App\Exchanging\Exception\ExchangeStaleRateException;
use App\Exchanging\ProviderInterface\ExchangeRateProviderInterface;
use App\Exchanging\ProviderInterface\ExchangeRemoteRateProviderInterface;
use App\Exchanging\Service\ExchangeAppliedRateAuditService;
use App\Exchanging\Service\ExchangeRateFreshnessService;
use App\Exchanging\Service\ExchangeRateProviderRegistry;
use App\Exchanging\Service\ExchangeRemoteRateProviderRegistry;
use App\Exchanging\Service\ExchangeRequestValidationService;
use App\Exchanging\ValueObject\ExchangeCurrencyPair;
use PHPUnit\Framework\TestCase;

final class ExchangeCriticalServicesTest extends TestCase
{
    public function testFreshnessBoundaryAndFutureCaptureAreFresh(): void
    {
        $service = new ExchangeRateFreshnessService(60);
        $reference = new \DateTimeImmutable('2026-09-20T12:00:00+00:00');

        $boundary = $service->inspect(new ExchangeRateFreshnessRequestDTO(
            'USD',
            'EUR',
            'test',
            new \DateTimeImmutable('2026-09-20'),
            $reference->modify('-60 seconds'),
            $reference,
        ));
        self::assertTrue($boundary->fresh);
        self::assertSame(60, $boundary->ageSeconds);
        self::assertSame('fresh', $boundary->reason);

        $future = $service->inspect(new ExchangeRateFreshnessRequestDTO(
            'USD',
            'EUR',
            'test',
            new \DateTimeImmutable('2026-09-20'),
            $reference->modify('+10 seconds'),
            $reference,
        ));
        self::assertTrue($future->fresh);
        self::assertSame(0, $future->ageSeconds);
    }

    public function testStaleRateIsReportedAndRejected(): void
    {
        $service = new ExchangeRateFreshnessService(60);
        $request = new ExchangeRateFreshnessRequestDTO(
            'USD',
            'EUR',
            'test',
            new \DateTimeImmutable('2026-09-20'),
            new \DateTimeImmutable('2026-09-20T11:58:59+00:00'),
            new \DateTimeImmutable('2026-09-20T12:00:00+00:00'),
        );

        $result = $service->inspect($request);
        self::assertFalse($result->fresh);
        self::assertSame(61, $result->ageSeconds);
        self::assertStringContainsString('exceeds max age 60 seconds', $result->reason);

        $this->expectException(ExchangeStaleRateException::class);
        $service->assertFresh($request);
    }

    public function testCaptureValidationNormalizesValidInput(): void
    {
        $result = (new ExchangeRequestValidationService())->validateRateCaptureRequest(
            new ExchangeRateCaptureRequestDTO(' usd ', ' eur ', '1.2500000000', ' provider-a '),
        );

        self::assertSame('USD', $result->baseCurrencyCode);
        self::assertSame('EUR', $result->quoteCurrencyCode);
        self::assertSame('1.2500000000', $result->rateValue);
        self::assertSame('provider-a', $result->providerCode);
    }

    public function testCaptureValidationAggregatesIndependentViolations(): void
    {
        $service = new ExchangeRequestValidationService();

        try {
            $service->validateRateCaptureRequest(new ExchangeRateCaptureRequestDTO('US', 'EURO', '0', '   '));
            self::fail('Invalid capture request must be rejected.');
        } catch (ExchangeInvalidRequestException $exception) {
            self::assertStringContainsString('baseCurrencyCode:', $exception->getMessage());
            self::assertStringContainsString('quoteCurrencyCode:', $exception->getMessage());
            self::assertStringContainsString('rateValue:', $exception->getMessage());
            self::assertStringContainsString('providerCode:', $exception->getMessage());
        }
    }

    public function testAppliedRateAuditIsNormalizedAndDeterministic(): void
    {
        $request = new ExchangeAppliedRateAuditRequestDTO(
            ' usd ',
            ' eur ',
            '10.00',
            '12.50',
            '1.2500000000',
            ' provider-a ',
            new \DateTimeImmutable('2026-09-20'),
            new \DateTimeImmutable('2026-09-20T12:00:00+00:00'),
            ' checkout ',
            ' order-42 ',
        );

        $service = new ExchangeAppliedRateAuditService();
        $first = $service->buildAudit($request);
        $second = $service->buildAudit($request);

        self::assertSame($first->auditKey, $second->auditKey);
        self::assertSame('USD', $first->baseCurrencyCode);
        self::assertSame('EUR', $first->quoteCurrencyCode);
        self::assertSame('provider-a', $first->providerCode);
        self::assertSame('checkout', $first->consumerContext);
        self::assertSame('order-42', $first->consumerReference);
        self::assertSame('USD', $first->auditPayload['baseCurrencyCode']);
        self::assertSame(64, strlen($first->auditKey));
    }

    public function testAppliedRateAuditRejectsMissingConsumerReference(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('consumerReference is required.');

        (new ExchangeAppliedRateAuditService())->buildAudit(new ExchangeAppliedRateAuditRequestDTO(
            'USD',
            'EUR',
            '10',
            '12.5',
            '1.25',
            'provider-a',
            new \DateTimeImmutable('2026-09-20'),
            new \DateTimeImmutable('2026-09-20T12:00:00+00:00'),
            'checkout',
            ' ',
        ));
    }

    public function testRateProviderRegistryFallsBackAfterUnsupportedAndEmptyProviders(): void
    {
        $request = new ExchangeRateLookupRequestDTO('USD', 'EUR');
        $result = new ExchangeRateLookupResultDTO(
            ExchangeCurrencyPair::fromStrings('USD', 'EUR'),
            '1.25',
            'winner',
            new \DateTimeImmutable('2026-09-20'),
            new \DateTimeImmutable('2026-09-20T12:00:00+00:00'),
        );

        $providers = [
            $this->rateProvider(false, null),
            $this->rateProvider(true, null),
            $this->rateProvider(true, $result),
        ];
        $registry = new ExchangeRateProviderRegistry($providers);

        self::assertSame(3, $registry->providerCount());
        self::assertSame($result, $registry->lookup($request));
    }

    public function testRateProviderRegistryReturnsNullWhenNothingResolves(): void
    {
        $registry = new ExchangeRateProviderRegistry([
            $this->rateProvider(false, null),
            $this->rateProvider(true, null),
        ]);

        self::assertNull($registry->lookup(new ExchangeRateLookupRequestDTO('USD', 'EUR')));
    }

    public function testRemoteProviderRegistrySelectsMatchAndRejectsUnknownProvider(): void
    {
        $provider = new class () implements ExchangeRemoteRateProviderInterface {
            public function providerCode(): string
            {
                return 'provider-a';
            }

            public function supports(ExchangeRemoteRateFetchRequestDTO $request): bool
            {
                return 'provider-a' === $request->providerCode;
            }

            public function fetch(ExchangeRemoteRateFetchRequestDTO $request): ExchangeRemoteRateFetchResultDTO
            {
                return new ExchangeRemoteRateFetchResultDTO(
                    $request->baseCurrencyCode,
                    $request->quoteCurrencyCode,
                    '1.25',
                    $this->providerCode(),
                    $request->rateDate ?? new \DateTimeImmutable('2026-09-20'),
                    new \DateTimeImmutable('2026-09-20T12:00:00+00:00'),
                );
            }
        };

        $registry = new ExchangeRemoteRateProviderRegistry([$provider]);
        self::assertSame(1, $registry->providerCount());
        self::assertSame(
            $provider,
            $registry->providerFor(new ExchangeRemoteRateFetchRequestDTO('USD', 'EUR', 'provider-a')),
        );

        $this->expectException(ExchangeRemoteRateFetchException::class);
        $this->expectExceptionMessage('not supported');
        $registry->providerFor(new ExchangeRemoteRateFetchRequestDTO('USD', 'EUR', 'unknown'));
    }

    private function rateProvider(
        bool $supports,
        ?ExchangeRateLookupResultDTO $result,
    ): ExchangeRateProviderInterface {
        return new class ($supports, $result) implements ExchangeRateProviderInterface {
            public function __construct(
                private readonly bool $supports,
                private readonly ?ExchangeRateLookupResultDTO $result,
            ) {
            }

            public function providerCode(): string
            {
                return 'test';
            }

            public function supports(ExchangeRateLookupRequestDTO $request): bool
            {
                return $this->supports;
            }

            public function lookup(ExchangeRateLookupRequestDTO $request): ?ExchangeRateLookupResultDTO
            {
                return $this->result;
            }
        };
    }
}

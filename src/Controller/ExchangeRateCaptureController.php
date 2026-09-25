<?php

declare(strict_types=1);

namespace App\Exchanging\Controller;

use App\Exchanging\DTO\ExchangeRateCaptureRequestDTO;
use App\Exchanging\Exception\ExchangeInvalidRequestException;
use App\Exchanging\ServiceInterface\ExchangeRateCaptureServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ExchangeRateCaptureController
{
    public function __construct(private ExchangeRateCaptureServiceInterface $exchangeRateCaptureService)
    {
    }

    #[Route('/exchanging/rates/capture', name: 'exchanging_rate_capture', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payload = $this->decodePayload($request);
            $result = $this->exchangeRateCaptureService->capture(new ExchangeRateCaptureRequestDTO(
                (string) ($payload['baseCurrencyCode'] ?? ''),
                (string) ($payload['quoteCurrencyCode'] ?? ''),
                (string) ($payload['rateValue'] ?? ''),
                (string) ($payload['providerCode'] ?? ''),
                $this->optionalDate($payload['rateDate'] ?? null),
            ));

            return new JsonResponse([
                'exchangeRateId' => $result->exchangeRateId,
                'baseCurrencyCode' => $result->baseCurrencyCode,
                'quoteCurrencyCode' => $result->quoteCurrencyCode,
                'rateValue' => $result->rateValue,
                'providerCode' => $result->providerCode,
                'rateDate' => $result->rateDate->format('Y-m-d'),
                'capturedAtImmutable' => $result->capturedAtImmutable->format(DATE_ATOM),
            ], JsonResponse::HTTP_CREATED);
        } catch (ExchangeInvalidRequestException|\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decodePayload(Request $request): array
    {
        $decoded = json_decode($request->getContent(), true);

        if (!is_array($decoded)) {
            throw new \InvalidArgumentException('JSON object payload is required.');
        }

        return $decoded;
    }

    private function optionalDate(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new \DateTimeImmutable((string) $value);
    }
}

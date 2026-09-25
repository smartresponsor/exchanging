<?php

declare(strict_types=1);

namespace App\Exchanging\Controller;

use App\Exchanging\DTO\ExchangeQuoteRequestDTO;
use App\Exchanging\Exception\ExchangeInvalidRequestException;
use App\Exchanging\Exception\ExchangeRateNotFoundException;
use App\Exchanging\Exception\ExchangeStaleRateException;
use App\Exchanging\ServiceInterface\ExchangeQuoteServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ExchangeQuoteController
{
    public function __construct(private ExchangeQuoteServiceInterface $exchangeQuoteService)
    {
    }

    #[Route('/exchanging/quote', name: 'exchanging_quote', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $payload = $this->decodePayload($request);
            $quote = $this->exchangeQuoteService->quote(new ExchangeQuoteRequestDTO(
                (string) ($payload['baseCurrencyCode'] ?? ''),
                (string) ($payload['quoteCurrencyCode'] ?? ''),
                (string) ($payload['amount'] ?? ''),
                $this->optionalDate($payload['rateDate'] ?? null),
            ));

            return new JsonResponse([
                'baseCurrencyCode' => $quote->pair()->baseCurrencyCode(),
                'quoteCurrencyCode' => $quote->pair()->quoteCurrencyCode(),
                'amount' => $quote->sourceAmount(),
                'rateValue' => $quote->rateValue(),
                'convertedAmount' => $quote->convertedAmount(),
                'providerCode' => $quote->providerCode(),
                'rateDate' => $quote->rateDate()->format('Y-m-d'),
                'capturedAtImmutable' => $quote->capturedAtImmutable()->format(DATE_ATOM),
            ]);
        } catch (ExchangeRateNotFoundException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (ExchangeStaleRateException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], JsonResponse::HTTP_CONFLICT);
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

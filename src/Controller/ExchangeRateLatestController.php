<?php

declare(strict_types=1);

namespace App\Exchanging\Controller;

use App\Exchanging\DTO\ExchangeRateReadRequestDTO;
use App\Exchanging\ServiceInterface\ExchangeRateReadServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ExchangeRateLatestController
{
    public function __construct(private ExchangeRateReadServiceInterface $exchangeRateReadService)
    {
    }

    #[Route('/exchanging/rates/latest', name: 'exchanging_rate_latest', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $result = $this->exchangeRateReadService->latest(new ExchangeRateReadRequestDTO(
                $this->optionalString($request->query->get('base')),
                $this->optionalString($request->query->get('quote')),
                $this->optionalString($request->query->get('provider')),
                max(1, min(100, $request->query->getInt('limit', 20))),
            ));

            $items = [];
            foreach ($result->items as $item) {
                $items[] = [
                    'exchangeRateId' => $item->exchangeRateId,
                    'baseCurrencyCode' => $item->baseCurrencyCode,
                    'quoteCurrencyCode' => $item->quoteCurrencyCode,
                    'rateValue' => $item->rateValue,
                    'providerCode' => $item->providerCode,
                    'rateDate' => $item->rateDate->format('Y-m-d'),
                    'capturedAtImmutable' => $item->capturedAtImmutable->format(DATE_ATOM),
                ];
            }

            return new JsonResponse([
                'count' => $result->count,
                'items' => $items,
            ]);
        } catch (\InvalidArgumentException $exception) {
            return new JsonResponse(['error' => $exception->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    private function optionalString(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }
}

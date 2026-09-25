<?php

declare(strict_types=1);

namespace App\Exchanging\Controller;

use App\Exchanging\ServiceInterface\ExchangeHealthCheckServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ExchangeHealthCheckController
{
    public function __construct(private ExchangeHealthCheckServiceInterface $exchangeHealthCheckService)
    {
    }

    #[Route('/exchanging/health', name: 'exchanging_health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $result = $this->exchangeHealthCheckService->check();

        $items = [];
        foreach ($result->items as $item) {
            $items[] = [
                'nameEntity' => $item->nameEntity,
                'passed' => $item->passed,
                'message' => $item->message,
            ];
        }

        return new JsonResponse([
            'healthy' => $result->healthy,
            'items' => $items,
            'checkedAtImmutable' => $result->checkedAtImmutable->format(DATE_ATOM),
        ], $result->healthy ? JsonResponse::HTTP_OK : JsonResponse::HTTP_SERVICE_UNAVAILABLE);
    }
}

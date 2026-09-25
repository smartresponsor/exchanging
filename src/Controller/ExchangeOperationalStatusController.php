<?php

declare(strict_types=1);

namespace App\Exchanging\Controller;

use App\Exchanging\ServiceInterface\ExchangeOperationalStatusServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ExchangeOperationalStatusController
{
    public function __construct(private ExchangeOperationalStatusServiceInterface $exchangeOperationalStatusService)
    {
    }

    #[Route('/exchanging/status', name: 'exchanging_status', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        $status = $this->exchangeOperationalStatusService->status();

        return new JsonResponse([
            'component' => $status->component,
            'packageName' => $status->packageName,
            'namespace' => $status->namespace,
            'tablePrefix' => $status->tablePrefix,
            'serviceInterfaces' => $status->serviceInterfaces,
            'httpEndpoints' => $status->httpEndpoints,
            'consoleCommands' => $status->consoleCommands,
            'policies' => $status->policies,
            'reportedAtImmutable' => $status->reportedAtImmutable->format(DATE_ATOM),
        ]);
    }
}

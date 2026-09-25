<?php

declare(strict_types=1);

namespace App\Exchanging\DTO;

final readonly class ExchangeOperationalStatusDTO
{
    /**
     * @param list<string> $serviceInterfaces
     * @param list<string> $httpEndpoints
     * @param list<string> $consoleCommands
     * @param array<string, mixed> $policies
     */
    public function __construct(
        public string $component,
        public string $packageName,
        public string $namespace,
        public string $tablePrefix,
        public array $serviceInterfaces,
        public array $httpEndpoints,
        public array $consoleCommands,
        public array $policies,
        public \DateTimeImmutable $reportedAtImmutable,
    ) {
    }
}

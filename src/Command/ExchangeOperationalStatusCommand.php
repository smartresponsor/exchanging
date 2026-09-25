<?php

declare(strict_types=1);

namespace App\Exchanging\Command;

use App\Exchanging\ServiceInterface\ExchangeOperationalStatusServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'exchanging:status',
    description: 'Show Exchanging operational status and integration surface.',
)]
final class ExchangeOperationalStatusCommand extends Command
{
    public function __construct(private readonly ExchangeOperationalStatusServiceInterface $exchangeOperationalStatusService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $status = $this->exchangeOperationalStatusService->status();
        $io = new SymfonyStyle($input, $output);

        $io->title('Exchanging operational status');
        $io->definitionList(
            ['component' => $status->component],
            ['packageName' => $status->packageName],
            ['namespace' => $status->namespace],
            ['tablePrefix' => $status->tablePrefix],
            ['reportedAtImmutable' => $status->reportedAtImmutable->format(DATE_ATOM)],
        );

        $io->section('Policies');
        $io->table(
            ['Policy', 'Value'],
            array_map(
                static fn (string $key, mixed $value): array => [$key, is_bool($value) ? ($value ? 'true' : 'false') : (string) $value],
                array_keys($status->policies),
                array_values($status->policies),
            ),
        );

        $io->section('HTTP endpoints');
        $io->listing($status->httpEndpoints);

        $io->section('Console commands');
        $io->listing($status->consoleCommands);

        return Command::SUCCESS;
    }
}

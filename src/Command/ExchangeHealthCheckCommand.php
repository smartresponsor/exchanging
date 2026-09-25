<?php

declare(strict_types=1);

namespace App\Exchanging\Command;

use App\Exchanging\ServiceInterface\ExchangeHealthCheckServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'exchanging:health',
    description: 'Run Exchanging lightweight wiring health checks.',
)]
final class ExchangeHealthCheckCommand extends Command
{
    public function __construct(private readonly ExchangeHealthCheckServiceInterface $exchangeHealthCheckService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->exchangeHealthCheckService->check();
        $io = new SymfonyStyle($input, $output);

        $rows = [];
        foreach ($result->items as $item) {
            $rows[] = [
                $item->nameEntity,
                $item->passed ? 'passed' : 'failed',
                $item->message,
            ];
        }

        $io->title('Exchanging health check');
        $io->table(['Check', 'Status', 'Message'], $rows);

        if (!$result->healthy) {
            $io->error('Exchanging health check failed.');

            return Command::FAILURE;
        }

        $io->success('Exchanging health check passed.');

        return Command::SUCCESS;
    }
}

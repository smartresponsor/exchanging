<?php

declare(strict_types=1);

namespace App\Exchanging\Command;

use App\Exchanging\ServiceInterface\ExchangeDemoRateSeedServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'exchanging:demo:seed-rates',
    description: 'Seed deterministic demo exchange rates for local host-app smoke flows.',
)]
final class ExchangeDemoRateSeedCommand extends Command
{
    public function __construct(private readonly ExchangeDemoRateSeedServiceInterface $exchangeDemoRateSeedService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $count = $this->exchangeDemoRateSeedService->seedDefaults();

            $io->success(sprintf('Seeded %d demo exchange rates.', $count));

            return Command::SUCCESS;
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            return Command::FAILURE;
        }
    }
}

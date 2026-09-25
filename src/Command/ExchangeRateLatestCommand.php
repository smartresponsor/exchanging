<?php

declare(strict_types=1);

namespace App\Exchanging\Command;

use App\Exchanging\DTO\ExchangeRateReadRequestDTO;
use App\Exchanging\ServiceInterface\ExchangeRateReadServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'exchanging:rate:latest',
    description: 'List latest exchange rate snapshots.',
)]
final class ExchangeRateLatestCommand extends Command
{
    public function __construct(private readonly ExchangeRateReadServiceInterface $exchangeRateReadService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('base', null, InputOption::VALUE_REQUIRED, 'Base currency code filter.')
            ->addOption('quote', null, InputOption::VALUE_REQUIRED, 'Quote currency code filter.')
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Provider code filter.')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum rows to return.', 20);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $result = $this->exchangeRateReadService->latest(new ExchangeRateReadRequestDTO(
                $this->optionalString($input->getOption('base')),
                $this->optionalString($input->getOption('quote')),
                $this->optionalString($input->getOption('provider')),
                max(1, min(100, (int) $input->getOption('limit'))),
            ));

            $rows = [];
            foreach ($result->items as $item) {
                $rows[] = [
                    $item->exchangeRateId,
                    $item->baseCurrencyCode . '/' . $item->quoteCurrencyCode,
                    $item->rateValue,
                    $item->providerCode,
                    $item->rateDate->format('Y-m-d'),
                    $item->capturedAtImmutable->format(DATE_ATOM),
                ];
            }

            $io->title(sprintf('Latest exchange rates: %d', $result->count));
            $io->table(
                ['ID', 'Pair', 'Rate', 'Provider', 'Rate date', 'Captured at'],
                $rows,
            );

            return Command::SUCCESS;
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            return Command::FAILURE;
        }
    }

    private function optionalString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}

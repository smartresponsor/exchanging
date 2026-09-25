<?php

declare(strict_types=1);

namespace App\Exchanging\Command;

use App\Exchanging\DTO\ExchangeRemoteRateFetchRequestDTO;
use App\Exchanging\ServiceInterface\ExchangeRemoteRateFetchServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'exchanging:rate:fetch',
    description: 'Fetch an exchange rate from a configured remote provider without persisting it.',
)]
final class ExchangeRemoteRateFetchCommand extends Command
{
    public function __construct(private readonly ExchangeRemoteRateFetchServiceInterface $exchangeRemoteRateFetchService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('base-currency-code', InputArgument::REQUIRED, 'Base currency code, for example USD.')
            ->addArgument('quote-currency-code', InputArgument::REQUIRED, 'Quote currency code, for example UAH.')
            ->addArgument('provider-code', InputArgument::REQUIRED, 'Provider code.')
            ->addOption('rate-date', null, InputOption::VALUE_REQUIRED, 'Rate date in YYYY-MM-DD format. Defaults to provider behavior.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $result = $this->exchangeRemoteRateFetchService->fetch(new ExchangeRemoteRateFetchRequestDTO(
                (string) $input->getArgument('base-currency-code'),
                (string) $input->getArgument('quote-currency-code'),
                (string) $input->getArgument('provider-code'),
                $this->optionalDate($input->getOption('rate-date')),
            ));

            $io->success('Exchange rate fetched.');
            $io->table(
                ['Field', 'Value'],
                [
                    ['baseCurrencyCode', $result->baseCurrencyCode],
                    ['quoteCurrencyCode', $result->quoteCurrencyCode],
                    ['rateValue', $result->rateValue],
                    ['providerCode', $result->providerCode],
                    ['rateDate', $result->rateDate->format('Y-m-d')],
                    ['fetchedAtImmutable', $result->fetchedAtImmutable->format(DATE_ATOM)],
                ],
            );

            return Command::SUCCESS;
        } catch (\Throwable $throwable) {
            $io->error($throwable->getMessage());

            return Command::FAILURE;
        }
    }

    private function optionalDate(mixed $value): ?\DateTimeImmutable
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new \DateTimeImmutable((string) $value);
    }
}

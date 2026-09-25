<?php

declare(strict_types=1);

namespace App\Exchanging\Command;

use App\Exchanging\DTO\ExchangeRateCaptureRequestDTO;
use App\Exchanging\ServiceInterface\ExchangeRateCaptureServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'exchanging:rate:capture',
    description: 'Capture an exchange rate snapshot.',
)]
final class ExchangeRateCaptureCommand extends Command
{
    public function __construct(private readonly ExchangeRateCaptureServiceInterface $exchangeRateCaptureService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('base-currency-code', InputArgument::REQUIRED, 'Base currency code, for example USD.')
            ->addArgument('quote-currency-code', InputArgument::REQUIRED, 'Quote currency code, for example UAH.')
            ->addArgument('rate-value', InputArgument::REQUIRED, 'Positive decimal exchange rate value.')
            ->addArgument('provider-code', InputArgument::REQUIRED, 'Provider code, for example manual or nbu.')
            ->addOption('rate-date', null, InputOption::VALUE_REQUIRED, 'Rate date in YYYY-MM-DD format. Defaults to today.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $rateDate = $this->optionalDate($input->getOption('rate-date'));
            $result = $this->exchangeRateCaptureService->capture(new ExchangeRateCaptureRequestDTO(
                (string) $input->getArgument('base-currency-code'),
                (string) $input->getArgument('quote-currency-code'),
                (string) $input->getArgument('rate-value'),
                (string) $input->getArgument('provider-code'),
                $rateDate,
            ));

            $io->success('Exchange rate snapshot captured.');
            $io->table(
                ['Field', 'Value'],
                [
                    ['exchangeRateId', $result->exchangeRateId],
                    ['baseCurrencyCode', $result->baseCurrencyCode],
                    ['quoteCurrencyCode', $result->quoteCurrencyCode],
                    ['rateValue', $result->rateValue],
                    ['providerCode', $result->providerCode],
                    ['rateDate', $result->rateDate->format('Y-m-d')],
                    ['capturedAtImmutable', $result->capturedAtImmutable->format(DATE_ATOM)],
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

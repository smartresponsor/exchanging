<?php

declare(strict_types=1);

namespace App\Exchanging\Command;

use App\Exchanging\DTO\ExchangeQuoteRequestDTO;
use App\Exchanging\ServiceInterface\ExchangeQuoteServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'exchanging:quote',
    description: 'Calculate an exchange quote from a captured or configured rate.',
)]
final class ExchangeQuoteCommand extends Command
{
    public function __construct(private readonly ExchangeQuoteServiceInterface $exchangeQuoteService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('base-currency-code', InputArgument::REQUIRED, 'Base currency code, for example USD.')
            ->addArgument('quote-currency-code', InputArgument::REQUIRED, 'Quote currency code, for example UAH.')
            ->addArgument('amount', InputArgument::REQUIRED, 'Positive decimal amount.')
            ->addOption('rate-date', null, InputOption::VALUE_REQUIRED, 'Rate date in YYYY-MM-DD format. Uses latest available when omitted.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $quote = $this->exchangeQuoteService->quote(new ExchangeQuoteRequestDTO(
                (string) $input->getArgument('base-currency-code'),
                (string) $input->getArgument('quote-currency-code'),
                (string) $input->getArgument('amount'),
                $this->optionalDate($input->getOption('rate-date')),
            ));

            $io->success('Exchange quote calculated.');
            $io->table(
                ['Field', 'Value'],
                [
                    ['baseCurrencyCode', $quote->pair()->baseCurrencyCode()],
                    ['quoteCurrencyCode', $quote->pair()->quoteCurrencyCode()],
                    ['amount', $quote->sourceAmount()],
                    ['rateValue', $quote->rateValue()],
                    ['convertedAmount', $quote->convertedAmount()],
                    ['providerCode', $quote->providerCode()],
                    ['rateDate', $quote->rateDate()->format('Y-m-d')],
                    ['capturedAtImmutable', $quote->capturedAtImmutable()->format(DATE_ATOM)],
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

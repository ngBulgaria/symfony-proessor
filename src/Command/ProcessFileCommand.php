<?php

declare(strict_types=1);

namespace App\Command;

use App\Interface\FileReaderInterface;
use App\Interface\BinLookupInterface;
use App\Interface\ExchangeRatesInterface;
use App\Interface\FeeCalculatorInterface;
use App\DTO\TransactionDTO;
use App\DTO\ExchangeRatesDTO;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Symfony Console Command for processing transactions from a file.
 */
class ProcessFileCommand extends Command
{
    protected static $defaultName = 'process:file';

    private FileReaderInterface $fileReader;
    private BinLookupInterface $binLookupService;
    private ExchangeRatesInterface $exchangeRatesService;
    private FeeCalculatorInterface $feeCalculator;
    private SymfonyStyle $io;
    private ?ExchangeRatesDTO $exchangeRates = null;

    /**
     * Constructor.
     *
     * @param FileReaderInterface $fileReader
     * @param BinLookupInterface $binLookupService
     * @param ExchangeRatesInterface $exchangeRatesService
     * @param FeeCalculatorInterface $feeCalculator
     */
    public function __construct(
        FileReaderInterface $fileReader,
        BinLookupInterface $binLookupService,
        ExchangeRatesInterface $exchangeRatesService,
        FeeCalculatorInterface $feeCalculator
    ) {
        parent::__construct();
        $this->fileReader = $fileReader;
        $this->binLookupService = $binLookupService;
        $this->exchangeRatesService = $exchangeRatesService;
        $this->feeCalculator = $feeCalculator;
    }

    /**
     * Configures the command arguments.
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Processes a file containing transactions.')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the file');
    }

    /**
     * Initializes the SymfonyStyle IO instance.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function initializeIO(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * Executes the command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int Command status code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->initializeIO($input, $output);
        $filePath = $input->getArgument('file');

        if (!is_string($filePath)) {
            $this->io->error('Invalid file path. Please provide a valid file.');
            return Command::FAILURE;
        }

        if (!$this->validateFilePath($filePath)) {
            return Command::FAILURE;
        }

        if (!$this->fetchExchangeRates()) {
            return Command::FAILURE;
        }

        $this->processTransactions($filePath);

        return Command::SUCCESS;
    }

    /**
     * Validates the provided file path.
     *
     * @param string $filePath
     *
     * @return bool Whether the file path is valid
     */
    private function validateFilePath(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            $this->io->error(sprintf('The file "%s" does not exist.', $filePath));
            return false;
        }

        return true;
    }

    /**
     * Fetches exchange rates from the API and stores it in a property.
     *
     * @return bool Returns true on success, false on failure.
     */
    private function fetchExchangeRates(): bool
    {
        try {
            $this->exchangeRates = $this->exchangeRatesService->getRates();
            return true;
        } catch (\Throwable $e) {
            $this->io->error('Failed to fetch exchange rates: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Processes transactions from the file.
     *
     * @param string $filePath
     */
    private function processTransactions(string $filePath): void
    {
        foreach ($this->fileReader->readLines($filePath) as $line) {
            $this->processTransaction($line);
        }
    }

    /**
     * Processes a single transaction.
     *
     * @param string $line JSON transaction data
     */
    private function processTransaction(string $line): void
    {
        try {
            $transaction = TransactionDTO::fromJson($line);
            $binInfo = $this->binLookupService->lookup($transaction->bin);
            $isEu = $binInfo->isEuCountry();

            if ($this->exchangeRates === null) {
                throw new \RuntimeException('Exchange rates have not been loaded.');
            }

            $rate = $this->exchangeRates->getRate($transaction->currency);
            $amountInEur = ($transaction->currency === 'EUR' || !$rate)
                ? $transaction->amount
                : $transaction->amount / $rate;

            $fee = $this->feeCalculator->calculate($amountInEur, $isEu);

            $this->io->success(sprintf('%.2f', $fee));
        } catch (\Throwable $e) {
            $this->io->error('Error processing transaction: ' . $e->getMessage());
        }
    }
}

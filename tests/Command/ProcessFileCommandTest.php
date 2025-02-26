<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\ProcessFileCommand;
use App\Interface\FileReaderInterface;
use App\Interface\BinLookupInterface;
use App\Interface\ExchangeRatesInterface;
use App\Interface\FeeCalculatorInterface;
use App\DTO\TransactionDTO;
use App\DTO\ExchangeRatesDTO;
use App\DTO\CountryDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProcessFileCommandTest extends TestCase
{
    private CommandTester $commandTester;
    private FileReaderInterface $fileReader;
    private BinLookupInterface $binLookup;
    private ExchangeRatesInterface $exchangeRates;
    private FeeCalculatorInterface $feeCalculator;
    private SymfonyStyle $io;
    private string $testFile;

    protected function setUp(): void
    {
        $this->fileReader = $this->createMock(FileReaderInterface::class);
        $this->binLookup = $this->createMock(BinLookupInterface::class);
        $this->exchangeRates = $this->createMock(ExchangeRatesInterface::class);
        $this->feeCalculator = $this->createMock(FeeCalculatorInterface::class);
        $this->testFile = sys_get_temp_dir() . '/transactions.txt';
        file_put_contents($this->testFile, ""); // Ensure the file exists

        $command = new ProcessFileCommand(
            $this->fileReader,
            $this->binLookup,
            $this->exchangeRates,
            $this->feeCalculator
        );

        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($command);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile); // Remove test file after each test
        }
    }

    public function testCommandFailsIfFileDoesNotExist(): void
    {
        // Run command with a non-existent file
        $this->commandTester->execute(['file' => 'non_existent_file.txt']);

        // Assert error output
        $this->assertStringContainsString(
            'The file "non_existent_file.txt" does not exist.',
            $this->commandTester->getDisplay()
        );

        // Assert failure exit code
        $this->assertSame(ProcessFileCommand::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testCommandFailsIfExchangeRatesCannotBeFetched(): void
    {
        // Ensure the file exists
        file_put_contents($this->testFile, "");

        // Simulate file reading
        $this->fileReader->method('readLines')->willReturn([]);

        // Simulate exchange rate failure
        $this->exchangeRates->method('getRates')
            ->willThrowException(new \RuntimeException('API error'));

        $this->commandTester->execute(['file' => $this->testFile]);

        $this->assertStringContainsString(
            'Failed to fetch exchange rates: API error',
            $this->commandTester->getDisplay()
        );

        $this->assertSame(ProcessFileCommand::FAILURE, $this->commandTester->getStatusCode());
    }

    public function testCommandProcessesTransactionsSuccessfully(): void
    {
        $mockTransactionJson = json_encode([
            'bin' => '45717360',
            'amount' => '100.00', // Should be a string for strict validation
            'currency' => 'USD',
        ]);

        file_put_contents($this->testFile, $mockTransactionJson);

        $this->fileReader->method('readLines')->willReturn([$mockTransactionJson]);

        $this->binLookup->method('lookup')->willReturn(new CountryDTO(alpha2: 'DE'));
        $this->exchangeRates->method('getRates')->willReturn(new ExchangeRatesDTO(
            success: true,
            timestamp: time(),
            base: 'EUR',
            date: '2024-02-26',
            rates: ['USD' => 1.1]
        ));
        $this->feeCalculator->method('calculate')->willReturn(1.82);

        $this->commandTester->execute(['file' => $this->testFile]);

        $this->assertStringContainsString('1.82', $this->commandTester->getDisplay());
        $this->assertSame(ProcessFileCommand::SUCCESS, $this->commandTester->getStatusCode());
    }

    public function testCommandHandlesTransactionProcessingErrors(): void
    {
        $mockTransactionJson = json_encode([
            'bin' => '45717360',
            'amount' => '100.00',
            'currency' => 'USD',
        ]);

        file_put_contents($this->testFile, $mockTransactionJson);

        $this->fileReader->method('readLines')->willReturn([$mockTransactionJson]);

        $this->binLookup->method('lookup')->willThrowException(new \RuntimeException('BIN lookup failed'));

        $this->commandTester->execute(['file' => $this->testFile]);

        $this->assertStringContainsString(
            'Error processing transaction: BIN lookup failed',
            $this->commandTester->getDisplay()
        );

        $this->assertSame(ProcessFileCommand::SUCCESS, $this->commandTester->getStatusCode());
    }
}

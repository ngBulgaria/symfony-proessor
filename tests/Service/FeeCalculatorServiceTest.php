<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\FeeCalculatorService;
use PHPUnit\Framework\TestCase;

class FeeCalculatorServiceTest extends TestCase
{
    private FeeCalculatorService $feeCalculatorService;
    private float $euFeeRate = 0.01;   // Example: 1% fee for EU transactions
    private float $nonEuFeeRate = 0.02; // Example: 2% fee for non-EU transactions

    protected function setUp(): void
    {
        $this->feeCalculatorService = new FeeCalculatorService($this->euFeeRate, $this->nonEuFeeRate);
    }

    public function testCalculateFeeForEuTransaction(): void
    {
        $amount = 100.0; // EUR
        $isEu = true;
        $expectedFee = $amount * $this->euFeeRate; // 100 * 0.01 = 1.0

        $calculatedFee = $this->feeCalculatorService->calculate($amount, $isEu);

        $this->assertSame($expectedFee, $calculatedFee);
    }

    public function testCalculateFeeForNonEuTransaction(): void
    {
        $amount = 100.0; // EUR
        $isEu = false;
        $expectedFee = $amount * $this->nonEuFeeRate; // 100 * 0.02 = 2.0

        $calculatedFee = $this->feeCalculatorService->calculate($amount, $isEu);

        $this->assertSame($expectedFee, $calculatedFee);
    }

    public function testCalculateFeeWithZeroAmount(): void
    {
        $amount = 0.0; // EUR
        $isEu = true;  // EU transaction
        $expectedFee = 0.0; // No fee for 0 amount

        $calculatedFee = $this->feeCalculatorService->calculate($amount, $isEu);

        $this->assertSame($expectedFee, $calculatedFee);
    }
}

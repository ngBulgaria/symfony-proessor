<?php

declare(strict_types=1);

namespace App\Service;

use App\Interface\FeeCalculatorInterface;

/**
 * Service implementing fee calculation logic.
 */
class FeeCalculatorService implements FeeCalculatorInterface
{
    private float $euFeeRate;
    private float $nonEuFeeRate;

    /**
     * @param float $euFeeRate   Configured fee rate for EU transactions
     * @param float $nonEuFeeRate Configured fee rate for non-EU transactions
     */
    public function __construct(float $euFeeRate, float $nonEuFeeRate)
    {
        $this->euFeeRate = $euFeeRate;
        $this->nonEuFeeRate = $nonEuFeeRate;
    }

    /**
     * Calculates the fee based on the amount and whether the transaction is from the EU.
     *
     * @param float $amountInEur Transaction amount in EUR
     * @param bool  $isEu        Whether the transaction is from an EU country
     *
     * @return float The calculated fee
     */
    public function calculate(float $amountInEur, bool $isEu): float
    {
        return $amountInEur * ($isEu ? $this->euFeeRate : $this->nonEuFeeRate);
    }
}

<?php

declare(strict_types=1);

namespace App\Interface;

/**
 * Interface for calculating transaction fees.
 */
interface FeeCalculatorInterface
{
    /**
     * Calculates the fee based on amount and EU status.
     *
     * @param float $amountInEur Transaction amount in EUR
     * @param bool  $isEu        Whether the transaction is from an EU country
     *
     * @return float The calculated fee
     */
    public function calculate(float $amountInEur, bool $isEu): float;
}

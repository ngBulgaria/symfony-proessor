<?php

declare(strict_types=1);

namespace App\DTO;

use App\Exception\InvalidArgumentException;

/**
 * Data Transfer Object representing a transaction.
 */
class TransactionDTO
{
    public readonly string $bin;
    public readonly float $amount;
    public readonly string $currency;

    /**
     * Private constructor to enforce use of static factory methods.
     *
     * @param string $bin
     * @param float  $amount
     * @param string $currency
     */
    private function __construct(string $bin, float $amount, string $currency)
    {
        $this->bin = $bin;
        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Creates a TransactionDTO from an associative array.
     *
     * @param array<string, mixed> $data
     *
     * @return self
     *
     * @throws InvalidArgumentException If required data is missing or invalid.
     */
    public static function fromArray(array $data): self
    {
        if (
            !isset($data['bin'], $data['amount'], $data['currency']) ||
            !is_string($data['bin']) ||
            !is_string($data['amount']) ||
            !is_string($data['currency']) ||
            !is_numeric($data['amount'])
        ) {
            throw new InvalidArgumentException(
                'Invalid transaction data. Required fields: bin (string), amount (numeric string), currency (string).'
            );
        }

        return new self(
            bin: $data['bin'],
            amount: (float) $data['amount'],
            currency: strtoupper($data['currency'])
        );
    }

    /**
     * Creates a TransactionDTO from a JSON string.
     *
     * @param string $json
     *
     * @return self
     *
     * @throws InvalidArgumentException If JSON decoding fails or data is invalid.
     */
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw new InvalidArgumentException('Invalid JSON provided: ' . json_last_error_msg());
        }

        return self::fromArray($data);
    }
}

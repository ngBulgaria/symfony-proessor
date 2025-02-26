<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * DTO representing Exchange Rates API response.
 */
class ExchangeRatesDTO
{
    public bool $success;
    public int $timestamp;
    public string $base;
    public string $date;
    /**
     * @var array<string, float>
     */
    public array $rates;

    /**
     * @param bool                $success   API response status
     * @param int                 $timestamp Unix timestamp of rates
     * @param string              $base      Base currency (e.g., EUR)
     * @param string              $date      Date of rates (e.g., 2025-02-22)
     * @param array<string,float> $rates     Currency rates array
     */
    public function __construct(
        bool $success,
        int $timestamp,
        string $base,
        string $date,
        array $rates
    ) {
        $this->success = $success;
        $this->timestamp = $timestamp;
        $this->base = $base;
        $this->date = $date;
        $this->rates = $rates;
    }

    /**
     * Retrieves the exchange rate for a given currency.
     *
     * @param string $currency
     * @return float|null
     */
    public function getRate(string $currency): ?float
    {
        return $this->rates[$currency] ?? null;
    }
}

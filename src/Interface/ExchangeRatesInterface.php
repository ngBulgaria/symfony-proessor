<?php

declare(strict_types=1);

namespace App\Interface;

use App\DTO\ExchangeRatesDTO;
use App\Exception\ApiException;

/**
 * Interface for Exchange Rates service.
 */
interface ExchangeRatesInterface
{
    /**
     * Retrieves the latest exchange rates.
     *
     * @return ExchangeRatesDTO
     *
     * @throws ApiException
     */
    public function getRates(): ExchangeRatesDTO;
}

<?php

declare(strict_types=1);

namespace App\Interface;

use App\DTO\CountryDTO;
use App\Exception\ApiException;

/**
 * Interface for BIN Lookup service.
 */
interface BinLookupInterface
{
    /**
     * Looks up BIN information for a given BIN number.
     *
     * @param string $bin
     *
     * @return CountryDTO
     *
     * @throws ApiException
     */
    public function lookup(string $bin): CountryDTO;
}

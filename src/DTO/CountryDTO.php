<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * DTO representing a country from the Bin Lookup API response.
 */
class CountryDTO
{
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE',
        'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT',
        'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO',
        'SE', 'SI', 'SK'
    ];

    public string $alpha2;

    /**
     * @param string $alpha2
     */
    public function __construct(
        string $alpha2,
    ) {
        $this->alpha2 = $alpha2;
    }

    /**
     * Checks if the card is from an EU country.
     *
     * @return bool
     */
    public function isEuCountry(): bool
    {
        return in_array($this->alpha2, CountryDTO::EU_COUNTRIES, true);
    }
}

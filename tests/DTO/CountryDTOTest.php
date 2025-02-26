<?php

namespace Tests\DTO;

use PHPUnit\Framework\TestCase;
use App\DTO\CountryDTO;

class CountryDTOTest extends TestCase
{
    public function testIsEuCountry(): void
    {
        $euCountry = new CountryDTO('FR');
        $nonEuCountry = new CountryDTO('US');

        $this->assertTrue(
            $euCountry->isEuCountry(),
            'France (FR) should be recognized as an EU country.'
        );
        $this->assertFalse(
            $nonEuCountry->isEuCountry(),
            'United States (US) should not be recognized as an EU country.'
        );
    }

    public function testIsEuCountryWithLowercase(): void
    {
        $euCountry = new CountryDTO('fr'); // Lowercase input
        $this->assertFalse(
            $euCountry->isEuCountry(),
            'Lowercase country codes should not match EU list (case-sensitive check).'
        );
    }

    public function testIsEuCountryWithInvalidCode(): void
    {
        $invalidCountry = new CountryDTO('ZZ'); // Non-existent country code
        $this->assertFalse(
            $invalidCountry->isEuCountry(),
            'Invalid country code should not be recognized as an EU country.'
        );
    }
}

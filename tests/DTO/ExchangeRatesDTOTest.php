<?php

namespace Tests\DTO;

use PHPUnit\Framework\TestCase;
use App\DTO\ExchangeRatesDTO;

class ExchangeRatesDTOTest extends TestCase
{
    public function testExchangeRatesDataTransferObjectCreation(): void
    {
        $dto = new ExchangeRatesDTO(
            success: true,
            timestamp: 1708819200,
            base: 'EUR',
            date: '2025-02-22',
            rates: ['USD' => 1.1, 'GBP' => 0.85]
        );

        $this->assertTrue($dto->success);
        $this->assertEquals(1708819200, $dto->timestamp);
        $this->assertEquals('EUR', $dto->base);
        $this->assertEquals('2025-02-22', $dto->date);
        $this->assertArrayHasKey('USD', $dto->rates);
        $this->assertArrayHasKey('GBP', $dto->rates);
    }

    public function testGetRate(): void
    {
        $dto = new ExchangeRatesDTO(
            success: true,
            timestamp: 1708819200,
            base: 'EUR',
            date: '2025-02-22',
            rates: ['USD' => 1.1, 'GBP' => 0.85]
        );

        $this->assertEquals(1.1, $dto->getRate('USD'));
        $this->assertEquals(0.85, $dto->getRate('GBP'));
        $this->assertNull($dto->getRate('JPY')); // Non-existent rate
    }
}

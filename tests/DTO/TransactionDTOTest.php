<?php

namespace Tests\DTO;

use PHPUnit\Framework\TestCase;
use App\DTO\TransactionDTO;
use App\Exception\InvalidArgumentException;

class TransactionDTOTest extends TestCase
{
    public function testFromJsonValid(): void
    {
        $json = '{"bin":"45717360","amount":"100.00","currency":"EUR"}';
        $dto = TransactionDTO::fromJson($json);

        $this->assertEquals("45717360", $dto->bin);
        $this->assertSame(100.00, $dto->amount);
        $this->assertEquals("EUR", $dto->currency);
    }

    public function testFromJsonInvalidJson(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON provided');

        TransactionDTO::fromJson('{invalid_json}');
    }

    public function testFromJsonMissingFields(): void
    {
        $json = '{"amount":50.00}';
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transaction data');

        TransactionDTO::fromJson($json);
    }

    public function testFromArrayValid(): void
    {
        $data = [
            'bin' => '45717360',
            'amount' => "200.50",
            'currency' => 'usd'
        ];
        $dto = TransactionDTO::fromArray($data);

        $this->assertEquals("45717360", $dto->bin);
        $this->assertSame(200.50, $dto->amount);
        $this->assertEquals("USD", $dto->currency);
    }

    public function testFromArrayMissingFields(): void
    {
        $data = [
            'amount' => 100.00,
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transaction data');

        TransactionDTO::fromArray($data);
    }

    public function testFromArrayInvalidTypes(): void
    {
        $data = [
            'bin' => 45717360, // Not a string
            'amount' => 'not_a_number', // Not numeric
            'currency' => 123 // Not a string
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid transaction data');

        TransactionDTO::fromArray($data);
    }
}

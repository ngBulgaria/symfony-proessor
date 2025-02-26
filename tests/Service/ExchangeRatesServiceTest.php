<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\DTO\ExchangeRatesDTO;
use App\Exception\ApiException;
use App\Exception\ApiResponseException;
use App\Interface\WebClientInterface;
use App\Service\ExchangeRatesService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ExchangeRatesServiceTest extends TestCase
{
    private ExchangeRatesService $exchangeRatesService;
    private WebClientInterface $webClient;
    private CacheInterface $cache;
    private ItemInterface $cacheItem;
    private string $apiUrl = 'https://api.example.com/latest';
    private int $cacheLifetime = 3600; // 1 hour

    protected function setUp(): void
    {
        $this->webClient = $this->createMock(WebClientInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->cacheItem = $this->createMock(ItemInterface::class);

        $this->exchangeRatesService = new ExchangeRatesService(
            $this->webClient,
            $this->apiUrl,
            $this->cache,
            $this->cacheLifetime
        );
    }

    public function testGetRatesReturnsValidExchangeRatesDTO(): void
    {
        $expectedDTO = new ExchangeRatesDTO(
            success: true,
            timestamp: 1640995200,
            base: 'USD',
            date: '2024-02-26',
            rates: ['EUR' => 0.89, 'GBP' => 0.75]
        );

        $cacheKey = 'exchange_rates_latest';

        // Simulate cache miss (so the closure is executed)
        $this->cache->method('get')
            ->with($cacheKey, $this->callback(fn($callback) => is_callable($callback)))
            ->willReturnCallback(function ($key, $callback) {
                return $callback($this->cacheItem);
            });

        // Simulate cache expiry
        $this->cacheItem->method('expiresAfter')->with($this->cacheLifetime);

        // Mock WebClient returning a valid API response
        $this->webClient->method('get')
            ->with($this->apiUrl)
            ->willReturn([
                'success' => true,
                'timestamp' => 1640995200,
                'base' => 'USD',
                'date' => '2024-02-26',
                'rates' => ['EUR' => 0.89, 'GBP' => 0.75]
            ]);

        // Call the method
        $result = $this->exchangeRatesService->getRates();

        // Assertions
        $this->assertInstanceOf(ExchangeRatesDTO::class, $result);
        $this->assertSame($expectedDTO->success, $result->success);
        $this->assertSame($expectedDTO->timestamp, $result->timestamp);
        $this->assertSame($expectedDTO->base, $result->base);
        $this->assertSame($expectedDTO->date, $result->date);
        $this->assertSame($expectedDTO->rates, $result->rates);
    }

    public function testGetRatesUsesCachedValue(): void
    {
        $expectedDTO = new ExchangeRatesDTO(
            success: true,
            timestamp: 1640995200,
            base: 'USD',
            date: '2024-02-26',
            rates: ['EUR' => 0.89, 'GBP' => 0.75]
        );

        $cacheKey = 'exchange_rates_latest';

        // Simulate cache hit (return cached DTO)
        $this->cache->method('get')
            ->with($cacheKey, $this->anything())
            ->willReturn($expectedDTO);

        // Call the method
        $result = $this->exchangeRatesService->getRates();

        // Assertions
        $this->assertSame($expectedDTO, $result);
        $this->webClient->expects($this->never())->method('get'); // Ensure API is NOT called
    }

    public function testGetRatesThrowsApiResponseExceptionOnInvalidResponse(): void
    {
        $cacheKey = 'exchange_rates_latest';

        // Simulate cache miss (calls API)
        $this->cache->method('get')
            ->with($cacheKey, $this->callback(fn($callback) => is_callable($callback)))
            ->willReturnCallback(function ($key, $callback) {
                return $callback($this->cacheItem);
            });

        // Mock WebClient returning an invalid response
        $this->webClient->method('get')
            ->with($this->apiUrl)
            ->willReturn(['error' => 'Invalid API key']);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage("Failed to fetch exchange rates.");

        try {
            $this->exchangeRatesService->getRates();
        } catch (ApiException $e) {
            // Ensure the inner exception is ApiResponseException
            $this->assertNotNull($e->getPrevious());
            $this->assertInstanceOf(ApiResponseException::class, $e->getPrevious());
            $this->assertStringContainsString("Invalid response from API", $e->getPrevious()->getMessage());

            throw $e; // Re-throw to satisfy PHPUnit's `expectException`
        }
    }

    public function testGetRatesThrowsApiExceptionOnFailure(): void
    {
        $cacheKey = 'exchange_rates_latest';

        // Simulate cache miss
        $this->cache->method('get')
            ->with($cacheKey, $this->callback(fn($callback) => is_callable($callback)))
            ->willReturnCallback(function ($key, $callback) {
                return $callback($this->cacheItem);
            });

        // Simulate API failure (exception in HTTP client)
        $this->webClient->method('get')
            ->with($this->apiUrl)
            ->willThrowException(new \Exception("Network error"));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage("Failed to fetch exchange rates.");

        // Call the method (should throw)
        $this->exchangeRatesService->getRates();
    }
}

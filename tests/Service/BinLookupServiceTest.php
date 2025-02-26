<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\DTO\CountryDTO;
use App\Exception\ApiResponseException;
use App\Service\BinLookupService;
use App\Interface\WebClientInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class BinLookupServiceTest extends TestCase
{
    private BinLookupService $binLookupService;
    private WebClientInterface $webClient;
    private CacheInterface $cache;
    private ItemInterface $cacheItem;
    private string $apiUrl = 'https://bin-api.com/lookup/';
    private int $cacheLifetime = 3600; // 1 hour

    protected function setUp(): void
    {
        $this->webClient = $this->createMock(WebClientInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->cacheItem = $this->createMock(ItemInterface::class);

        $this->binLookupService = new BinLookupService(
            $this->webClient,
            $this->apiUrl,
            $this->cache,
            $this->cacheLifetime
        );
    }

    public function testSuccessfulBinLookup(): void
    {
        $bin = '45717360';
        $cacheKey = 'bin_lookup_' . $bin;
        $expectedCountry = new CountryDTO(alpha2: 'US');

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
            ->with($this->apiUrl . $bin)
            ->willReturn(['country' => ['alpha2' => 'US']]);

        // Call the method
        $result = $this->binLookupService->lookup($bin);

        // Assertions
        $this->assertInstanceOf(CountryDTO::class, $result);
        $this->assertEquals($expectedCountry->alpha2, $result->alpha2);
    }

    public function testLookupReturnsCachedValue(): void
    {
        $bin = '45717360';
        $cacheKey = 'bin_lookup_' . $bin;
        $cachedValue = new CountryDTO(alpha2: 'CA');

        // Simulate cache hit (returns the cached CountryDTO)
        $this->cache->method('get')
            ->with($cacheKey, $this->anything())
            ->willReturn($cachedValue);

        // Call the method
        $result = $this->binLookupService->lookup($bin);

        // Assertions
        $this->assertSame($cachedValue, $result);
        $this->webClient->expects($this->never())->method('get'); // Ensure API is NOT called
    }

    public function testLookupThrowsExceptionOnInvalidResponse(): void
    {
        $bin = '45717360';
        $cacheKey = 'bin_lookup_' . $bin;

        // Simulate cache miss (calls API)
        $this->cache->method('get')
            ->with($cacheKey, $this->callback(fn($callback) => is_callable($callback)))
            ->willReturnCallback(function ($key, $callback) {
                return $callback($this->cacheItem);
            });

        // Mock WebClient returning an invalid response
        $this->webClient->method('get')
            ->with($this->apiUrl . $bin)
            ->willReturn(['unexpected_key' => 'no country']);

        // Expect the outer ApiResponseException
        $this->expectException(ApiResponseException::class);
        $this->expectExceptionMessage("Failed to fetch BIN details.");

        try {
            $this->binLookupService->lookup($bin);
        } catch (ApiResponseException $e) {
            // Ensure the inner exception has the "Invalid response from API" message
            $this->assertNotNull($e->getPrevious());
            $this->assertStringContainsString('Invalid response from API', $e->getPrevious()->getMessage());

            throw $e; // Re-throw to let PHPUnit handle it
        }
    }


    public function testLookupThrowsExceptionOnApiFailure(): void
    {
        $bin = '45717360';
        $cacheKey = 'bin_lookup_' . $bin;

        // Simulate cache miss
        $this->cache->method('get')
            ->with($cacheKey, $this->callback(fn($callback) => is_callable($callback)))
            ->willReturnCallback(function ($key, $callback) {
                return $callback($this->cacheItem);
            });

        // Simulate API failure (Exception)
        $this->webClient->method('get')
            ->with($this->apiUrl . $bin)
            ->willThrowException(new \Exception("Network error"));

        $this->expectException(ApiResponseException::class);
        $this->expectExceptionMessage("Failed to fetch BIN details.");

        // Call the method
        $this->binLookupService->lookup($bin);
    }
}

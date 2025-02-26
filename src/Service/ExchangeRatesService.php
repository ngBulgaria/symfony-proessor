<?php

declare(strict_types=1);

namespace App\Service;

use App\Interface\ExchangeRatesInterface;
use App\Interface\WebClientInterface;
use App\DTO\ExchangeRatesDTO;
use App\Exception\ApiException;
use App\Exception\ApiResponseException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Service to fetch exchange rates from an external API with caching.
 */
class ExchangeRatesService implements ExchangeRatesInterface
{
    private WebClientInterface $webClient;
    private string $apiUrl;
    private CacheInterface $cache;
    private int $cacheLifetime;

    /**
     * @param WebClientInterface  $webClient     HTTP client for API calls
     * @param string              $apiUrl        API URL including access key
     * @param CacheInterface      $cache         Cache service
     * @param int                 $cacheLifetime Cache lifetime
     */
    public function __construct(
        WebClientInterface $webClient,
        string $apiUrl,
        CacheInterface $cache,
        int $cacheLifetime
    ) {
        $this->webClient = $webClient;
        $this->apiUrl = $apiUrl;
        $this->cache = $cache;
        $this->cacheLifetime = $cacheLifetime;
    }

    /**
     * Retrieves the latest exchange rates.
     *
     * @return ExchangeRatesDTO
     *
     * @throws ApiException If the API response is invalid
     */
    public function getRates(): ExchangeRatesDTO
    {
        $cacheKey = 'exchange_rates_latest';

        return $this->cache->get($cacheKey, function (ItemInterface $item) {
            $item->expiresAfter($this->cacheLifetime);

            try {
                $response = $this->webClient->get($this->apiUrl);

                if (!array_key_exists('success', $response) || $response['success'] !== true) {
                    throw new ApiResponseException("Invalid response from API: {$this->apiUrl}");
                }

                return new ExchangeRatesDTO(
                    success: (bool) $response['success'],
                    timestamp: isset($response['timestamp']) && is_numeric($response['timestamp'])
                        ? (int) $response['timestamp']
                        : 0,
                    base: isset($response['base']) && is_string($response['base'])
                        ? $response['base']
                        : 'EUR',
                    date: isset($response['date']) && is_string($response['date'])
                        ? $response['date']
                        : '',
                    rates: isset($response['rates']) && is_array($response['rates'])
                        ? array_map(fn($rate) => (float) $rate, $response['rates'])
                        : []
                );
            } catch (\Throwable $e) {
                throw new ApiException("Failed to fetch exchange rates.", 0, $e);
            }
        });
    }
}

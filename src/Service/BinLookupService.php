<?php

declare(strict_types=1);

namespace App\Service;

use App\Interface\BinLookupInterface;
use App\Interface\WebClientInterface;
use App\DTO\CountryDTO;
use App\Exception\ApiResponseException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Service to fetch BIN details from an external API with caching.
 */
class BinLookupService implements BinLookupInterface
{
    private WebClientInterface $webClient;
    private string $apiUrl;
    private CacheInterface $cache;
    private int $cacheLifetime;

    /**
     * @param WebClientInterface  $webClient     HTTP client for API requests
     * @param string              $apiUrl        API base URL for BIN lookup
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
     * Looks up BIN details for a given BIN number.
     *
     * @param string $bin The BIN number to look up
     *
     * @return CountryDTO
     *
     * @throws ApiResponseException If the API response is invalid
     */
    public function lookup(string $bin): CountryDTO
    {
        $cacheKey = 'bin_lookup_' . $bin;

        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($bin) {
            $item->expiresAfter($this->cacheLifetime);

            $url = $this->apiUrl . $bin;

            try {
                $response = $this->webClient->get($url);

                // Validate API response
                if (!isset($response['country']) || !is_array($response['country'])) {
                    throw new ApiResponseException("Invalid response from API: {$url}");
                }

                $countryData = $response['country'];

                return new CountryDTO(
                    alpha2: is_string($countryData['alpha2'] ?? null) ? $countryData['alpha2'] : ''
                );
            } catch (\Throwable $e) {
                throw new ApiResponseException("Failed to fetch BIN details.", 0, $e);
            }
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Interface\WebClientInterface;
use App\Exception\ApiConnectionException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * WebClient implementation of WebClientInterface.
 */
class WebClientService implements WebClientInterface
{
    private HttpClientInterface $client;

    /**
     * @param HttpClientInterface $client HTTP client for making requests
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Sends a GET request and returns the response as an associative array.
     *
     * @param string $url The API endpoint to fetch data from
     *
     * @return array<string, mixed>
     *
     * @throws ApiConnectionException If the request fails
     */
    public function get(string $url): array
    {
        try {
            $response = $this->client->request('GET', $url);
            return $response->toArray();
        } catch (ExceptionInterface $e) {
            throw new ApiConnectionException("Failed to connect to API: {$url}", 0, $e);
        }
    }
}

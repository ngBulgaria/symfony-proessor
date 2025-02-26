<?php

declare(strict_types=1);

namespace App\Interface;

/**
 * Interface for making HTTP GET requests.
 */
interface WebClientInterface
{
    /**
     * Sends a GET request and returns the response as an associative array.
     *
     * @param string $url
     *
     * @return array<string, mixed>
     *
     * @throws \App\Exception\ApiException
     */
    public function get(string $url): array;
}

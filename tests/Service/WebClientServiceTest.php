<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\WebClientService;
use App\Interface\WebClientInterface;
use App\Exception\ApiConnectionException;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class WebClientServiceTest extends TestCase
{
    private WebClientInterface $webClientService;
    private HttpClientInterface $httpClient;
    private ResponseInterface $response;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);

        $this->webClientService = new WebClientService($this->httpClient);
    }

    public function testSuccessfulGetRequest(): void
    {
        $url = 'https://api.example.com/data';
        $expectedData = ['key' => 'value'];

        // Mock HTTP response
        $this->response->method('toArray')->willReturn($expectedData);

        // Mock HTTP client behavior
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', $url)
            ->willReturn($this->response);

        // Call the method
        $result = $this->webClientService->get($url);

        // Assert that the returned data is correct
        $this->assertSame($expectedData, $result);
    }

    public function testThrowsApiConnectionExceptionOnFailure(): void
    {
        $url = 'https://api.example.com/data';

        // Simulate an exception from the HTTP client
        $this->httpClient->method('request')
            ->with('GET', $url)
            ->willThrowException($this->createMock(ExceptionInterface::class));

        // Expect ApiConnectionException
        $this->expectException(ApiConnectionException::class);
        $this->expectExceptionMessage("Failed to connect to API: $url");

        // Call the method (should throw)
        $this->webClientService->get($url);
    }
}

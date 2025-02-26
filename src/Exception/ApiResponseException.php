<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown when an API returns an invalid response (e.g., bad JSON, unexpected format).
 */
class ApiResponseException extends ApiException
{
    public function __construct(
        string $message = "Invalid response received from the external API.",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

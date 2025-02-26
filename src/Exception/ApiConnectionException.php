<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown when an API connection error occurs (e.g., network issues, timeouts).
 */
class ApiConnectionException extends ApiException
{
    public function __construct(
        string $message = "Unable to connect to the external API.",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

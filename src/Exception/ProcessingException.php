<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown when an unexpected error occurs during data processing.
 */
class ProcessingException extends BaseException
{
    public function __construct(
        string $message = "An error occurred during data processing.",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

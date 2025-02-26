<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown when a specified file cannot be found.
 */
class FileNotFoundException extends FileException
{
    public function __construct(
        string $message = "The specified file was not found.",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

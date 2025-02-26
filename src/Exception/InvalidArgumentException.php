<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Custom exception for invalid argument errors.
 */
class InvalidArgumentException extends BaseException
{
    /**
     * @param string         $message  Error message
     * @param int            $code     Error code (default: 422)
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message = 'Invalid argument provided.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}

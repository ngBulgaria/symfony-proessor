<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use Throwable;

/**
 * Base exception class for the application.
 */
class BaseException extends Exception
{
    /**
     * @param string         $message  Error message
     * @param int            $code     Error code
     * @param Throwable|null $previous Previous throwable
     */
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

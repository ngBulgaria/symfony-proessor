<?php

declare(strict_types=1);

namespace App\Interface;

use App\Exception\FileException;

/**
 * Interface for reading files line by line.
 */
interface FileReaderInterface
{
    /**
     * Reads a file line by line.
     *
     * @param string $filePath
     *
     * @return iterable<string>
     *
     * @throws FileException
     */
    public function readLines(string $filePath): iterable;
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Interface\FileReaderInterface;
use App\Exception\FileNotFoundException;
use App\Exception\FileException;

/**
 * Service for reading files line by line.
 */
class FileReaderService implements FileReaderInterface
{
    /**
     * Reads a file line by line.
     *
     * @param string $filePath Path to the file
     *
     * @return iterable<string>
     *
     * @throws FileNotFoundException If the file does not exist
     * @throws FileException If the file cannot be opened
     */
    public function readLines(string $filePath): iterable
    {
        if (!file_exists($filePath)) {
            throw new FileNotFoundException("File not found: {$filePath}");
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new FileException("Cannot open file: {$filePath}");
        }

        try {
            while (($line = fgets($handle)) !== false) {
                yield trim($line);
            }
        } finally {
            fclose($handle);
        }
    }
}

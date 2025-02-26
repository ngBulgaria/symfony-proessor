<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\FileReaderService;
use App\Interface\FileReaderInterface;
use App\Exception\FileNotFoundException;
use PHPUnit\Framework\TestCase;

class FileReaderServiceTest extends TestCase
{
    private FileReaderInterface $fileReaderService;
    private string $testFile;

    protected function setUp(): void
    {
        $this->fileReaderService = new FileReaderService();
        $this->testFile = sys_get_temp_dir() . '/test_file.txt';
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile); // Remove temp file after test
        }
    }

    public function testReadsFileLineByLine(): void
    {
        file_put_contents($this->testFile, "Line 1\nLine 2\nLine 3\n");

        $lines = iterator_to_array($this->fileReaderService->readLines($this->testFile));

        $this->assertSame(['Line 1', 'Line 2', 'Line 3'], $lines);
    }

    public function testThrowsFileNotFoundException(): void
    {
        $nonExistentFile = sys_get_temp_dir() . '/non_existent_file.txt';

        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage("File not found: $nonExistentFile");

        iterator_to_array($this->fileReaderService->readLines($nonExistentFile));
    }
}

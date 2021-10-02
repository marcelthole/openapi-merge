<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\FileHandling;

use Mthole\OpenApiMerge\FileHandling\Exception\IOException;

use function dirname;
use function getcwd;
use function pathinfo;
use function realpath;
use function strpos;

use const DIRECTORY_SEPARATOR;
use const PATHINFO_EXTENSION;

final class File
{
    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function getFileExtension(): string
    {
        return pathinfo($this->filename, PATHINFO_EXTENSION);
    }

    public function getAbsoluteFile(): string
    {
        $fullFilename = realpath($this->filename);
        if ($fullFilename === false) {
            throw IOException::createWithNonExistingFile(
                $this->createAbsoluteFilePath($this->filename)
            );
        }

        return $fullFilename;
    }

    public function getAbsolutePath(): string
    {
        return dirname($this->getAbsoluteFile());
    }

    private function createAbsoluteFilePath(string $filename): string
    {
        if (strpos($filename, '/') === 0) {
            return $filename;
        }

        return getcwd() . DIRECTORY_SEPARATOR . $filename;
    }
}

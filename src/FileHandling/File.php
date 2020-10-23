<?php

declare(strict_types=1);

namespace OpenApiMerge\FileHandling;

use OpenApiMerge\FileHandling\Exception\IOException;

use function getcwd;
use function pathinfo;
use function realpath;

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

    public function getAbsolutePath(): string
    {
        $fullFilename = realpath($this->filename);
        if ($fullFilename === false) {
            throw IOException::createWithNonExistingFile(getcwd() . '/' . $this->filename);
        }

        return $fullFilename;
    }
}

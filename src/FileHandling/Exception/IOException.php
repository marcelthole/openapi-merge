<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\FileHandling\Exception;

use Exception;

use function sprintf;

class IOException extends Exception
{
    private string $filename;

    public static function createWithNonExistingFile(string $filename): self
    {
        $exception           = new IOException(
            sprintf('Given file "%s" was not found', $filename)
        );
        $exception->filename = $filename;

        return $exception;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}

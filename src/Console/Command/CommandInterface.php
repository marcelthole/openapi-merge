<?php

declare(strict_types=1);

namespace OpenApiMerge\Console\Command;

use OpenApiMerge\Console\IO\WriterInterface;
use OpenApiMerge\FileHandling\File;

interface CommandInterface
{
    /** @param File[] $additionalFiles */
    public function run(
        WriterInterface $io,
        File $baseFile,
        array $additionalFiles
    ): void;
}

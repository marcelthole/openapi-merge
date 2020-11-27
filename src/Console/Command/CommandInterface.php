<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Console\Command;

use Mthole\OpenApiMerge\Console\IO\WriterInterface;
use Mthole\OpenApiMerge\FileHandling\File;

interface CommandInterface
{
    /** @param File[] $additionalFiles */
    public function run(
        WriterInterface $io,
        File $baseFile,
        array $additionalFiles
    ): void;
}

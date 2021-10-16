<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge;

use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;

interface OpenApiMergeInterface
{
    /** @param list<File> $additionalFiles */
    public function mergeFiles(
        File $baseFile,
        array $additionalFiles,
        bool $resolveReference = true
    ): SpecificationFile;
}

<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge;

use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;

interface OpenApiMergeInterface
{
    public function mergeFiles(File $baseFile, File ...$additionalFiles): SpecificationFile;
}

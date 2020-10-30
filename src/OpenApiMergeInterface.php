<?php

declare(strict_types=1);

namespace OpenApiMerge;

use OpenApiMerge\FileHandling\File;
use OpenApiMerge\FileHandling\SpecificationFile;

interface OpenApiMergeInterface
{
    public function mergeFiles(File $baseFile, File ...$additionalFiles): SpecificationFile;
}

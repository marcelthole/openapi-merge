<?php

declare(strict_types=1);

namespace OpenApiMerge\Writer;

use OpenApiMerge\FileHandling\SpecificationFile;

interface DefinitionWriterInterface
{
    public function write(SpecificationFile $specFile): string;
}

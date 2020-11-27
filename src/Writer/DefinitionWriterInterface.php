<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Writer;

use Mthole\OpenApiMerge\FileHandling\SpecificationFile;

interface DefinitionWriterInterface
{
    public function write(SpecificationFile $specFile): string;
}

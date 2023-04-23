<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use cebe\openapi\spec\OpenApi;
use Mthole\OpenApiMerge\FileHandling\File;

final class ReferenceResolverResult
{
    /** @param list<File> $foundReferenceFiles */
    public function __construct(
        private OpenApi $openApiSpecification,
        private array $foundReferenceFiles,
    ) {
    }

    public function getNormalizedDefinition(): OpenApi
    {
        return $this->openApiSpecification;
    }

    /** @return list<File> */
    public function getFoundReferenceFiles(): array
    {
        return $this->foundReferenceFiles;
    }
}

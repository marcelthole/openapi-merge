<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use cebe\openapi\spec\OpenApi;
use Mthole\OpenApiMerge\FileHandling\File;

final class ReferenceResolverResult
{
    private OpenApi $openApiSpecification;
    /** @var array<int, File> */
    private array $foundReferenceFiles;

    /**
     * @param array<int, File> $foundReferenceFiles
     */
    public function __construct(
        OpenApi $openApiSpecification,
        array $foundReferenceFiles
    ) {
        $this->openApiSpecification = $openApiSpecification;
        $this->foundReferenceFiles  = $foundReferenceFiles;
    }

    public function getNormalizedDefinition(): OpenApi
    {
        return $this->openApiSpecification;
    }

    /** @return array<int, File> */
    public function getFoundReferenceFiles(): array
    {
        return $this->foundReferenceFiles;
    }
}

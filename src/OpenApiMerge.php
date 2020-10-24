<?php

declare(strict_types=1);

namespace OpenApiMerge;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Paths;
use OpenApiMerge\FileHandling\File;
use OpenApiMerge\FileHandling\SpecificationFile;
use OpenApiMerge\Reader\FileReader;

use function array_merge;
use function assert;

class OpenApiMerge
{
    private FileReader $openApiReader;

    public function __construct(FileReader $openApiReader)
    {
        $this->openApiReader = $openApiReader;
    }

    public function mergeFiles(File $baseFile, File ...$additionalFiles): SpecificationFile
    {
        $mergedOpenApiDefinition = $this->openApiReader->readFile($baseFile)->getOpenApiSpecificationObject();
        assert($mergedOpenApiDefinition instanceof OpenApi);

        foreach ($additionalFiles as $additionalFile) {
            $additionalDefinition = $this->openApiReader->readFile($additionalFile)->getOpenApiSpecificationObject();
            assert($additionalDefinition instanceof OpenApi);

            $mergedOpenApiDefinition->paths = new Paths(
                array_merge(
                    $mergedOpenApiDefinition->paths->getPaths(),
                    $additionalDefinition->paths->getPaths()
                )
            );
        }

        if ($mergedOpenApiDefinition->components !== null) {
            $mergedOpenApiDefinition->components->schemas = [];
        }

        return new SpecificationFile(
            $baseFile,
            $mergedOpenApiDefinition
        );
    }
}

<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge;

use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Merge\PathMergerInterface;
use Mthole\OpenApiMerge\Reader\FileReader;

class OpenApiMerge implements OpenApiMergeInterface
{
    private FileReader $openApiReader;

    private PathMergerInterface $pathMerger;

    public function __construct(
        FileReader $openApiReader,
        PathMergerInterface $pathMerger
    ) {
        $this->openApiReader = $openApiReader;
        $this->pathMerger    = $pathMerger;
    }

    public function mergeFiles(File $baseFile, File ...$additionalFiles): SpecificationFile
    {
        $mergedOpenApiDefinition = $this->openApiReader->readFile($baseFile)->getOpenApi();

        foreach ($additionalFiles as $additionalFile) {
            $additionalDefinition           = $this->openApiReader->readFile($additionalFile)->getOpenApi();
            $mergedOpenApiDefinition->paths = $this->pathMerger->mergePaths(
                $mergedOpenApiDefinition->paths,
                $additionalDefinition->paths
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

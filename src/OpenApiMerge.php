<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge;

use cebe\openapi\spec\Components;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Merge\PathMergerInterface;
use Mthole\OpenApiMerge\Merge\ReferenceNormalizer;
use Mthole\OpenApiMerge\Reader\FileReader;

use function array_merge;
use function array_push;
use function count;

class OpenApiMerge implements OpenApiMergeInterface
{
    private FileReader $openApiReader;

    private PathMergerInterface $pathMerger;
    private ReferenceNormalizer $referenceNormalizer;

    public function __construct(
        FileReader $openApiReader,
        PathMergerInterface $pathMerger,
        ReferenceNormalizer $referenceResolver
    ) {
        $this->openApiReader       = $openApiReader;
        $this->pathMerger          = $pathMerger;
        $this->referenceNormalizer = $referenceResolver;
    }

    /** @param list<File> $additionalFiles */
    public function mergeFiles(File $baseFile, array $additionalFiles, bool $resolveReference = true): SpecificationFile
    {
        $mergedOpenApiDefinition = $this->openApiReader->readFile($baseFile, $resolveReference)->getOpenApi();

        // use "for" instead of "foreach" to iterate over new added files
        for ($i = 0; $i < count($additionalFiles); $i++) {
            $additionalFile       = $additionalFiles[$i];
            $additionalDefinition = $this->openApiReader->readFile($additionalFile, $resolveReference)->getOpenApi();
            if (! $resolveReference) {
                $resolvedReferenceResult = $this->referenceNormalizer->normalizeInlineReferences(
                    $additionalFile,
                    $additionalDefinition
                );
                array_push($additionalFiles, ...$resolvedReferenceResult->getFoundReferenceFiles());
                $additionalDefinition = $resolvedReferenceResult->getNormalizedDefinition();
            }

            $mergedOpenApiDefinition->paths = $this->pathMerger->mergePaths(
                $mergedOpenApiDefinition->paths,
                $additionalDefinition->paths
            );

            if ($additionalDefinition->components === null) {
                continue;
            }

            if ($mergedOpenApiDefinition->components === null) {
                $mergedOpenApiDefinition->components = new Components([]);
            }

            $mergedOpenApiDefinition->components->schemas = array_merge(
                $mergedOpenApiDefinition->components->schemas ?? [],
                $additionalDefinition->components->schemas ?? []
            );
        }

        if ($resolveReference && $mergedOpenApiDefinition->components !== null) {
            $mergedOpenApiDefinition->components->schemas = [];
        }

        return new SpecificationFile(
            $baseFile,
            $mergedOpenApiDefinition
        );
    }
}

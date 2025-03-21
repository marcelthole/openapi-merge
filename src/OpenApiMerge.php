<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge;

use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Merge\MergerInterface;
use Mthole\OpenApiMerge\Merge\ReferenceNormalizer;
use Mthole\OpenApiMerge\Reader\FileReader;

use function array_flip;
use function array_key_exists;
use function array_map;
use function count;

class OpenApiMerge implements OpenApiMergeInterface
{
    /** @param list<MergerInterface> $merger */
    public function __construct(
        private FileReader $openApiReader,
        private array $merger,
        private ReferenceNormalizer $referenceNormalizer,
    ) {
    }

    /** @param array<array-key, File> $additionalFiles */
    public function mergeFiles(File $baseFile, array $additionalFiles, bool $resolveReference = true): SpecificationFile
    {
        $mergedOpenApiDefinition = $this->openApiReader->readFile($baseFile, $resolveReference)->getOpenApi();

        $additionalFileHashMap = array_flip(
            array_map(
                static fn (File $file): string => $file->getAbsoluteFile(),
                $additionalFiles,
            ),
        );

        // use "for" instead of "foreach" to iterate over new added files
        for ($i = 0; $i < count($additionalFiles); $i++) {
            $additionalFile       = $additionalFiles[$i];
            $additionalDefinition = $this->openApiReader->readFile($additionalFile, $resolveReference)->getOpenApi();
            if (! $resolveReference) {
                $resolvedReferenceResult = $this->referenceNormalizer->normalizeInlineReferences(
                    $additionalFile,
                    $additionalDefinition,
                );

                foreach ($resolvedReferenceResult->getFoundReferenceFiles() as $foundReferenceFile) {
                    if (array_key_exists($foundReferenceFile->getAbsoluteFile(), $additionalFileHashMap)) {
                        continue;
                    }

                    $additionalFiles[]                                             = $foundReferenceFile;
                    $additionalFileHashMap[$foundReferenceFile->getAbsoluteFile()] = true;
                }

                $additionalDefinition = $resolvedReferenceResult->getNormalizedDefinition();
            }

            foreach ($this->merger as $merger) {
                $merger->merge(
                    $mergedOpenApiDefinition,
                    $additionalDefinition,
                );
            }
        }

        if ($resolveReference && $mergedOpenApiDefinition->components !== null) {
            $mergedOpenApiDefinition->components->schemas       = [];
            $mergedOpenApiDefinition->components->responses     = [];
            $mergedOpenApiDefinition->components->requestBodies = [];
        }

        return new SpecificationFile(
            $baseFile,
            $mergedOpenApiDefinition,
        );
    }
}

<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Response;
use Mthole\OpenApiMerge\FileHandling\File;

use function array_map;
use function preg_match;

use const DIRECTORY_SEPARATOR;

class ReferenceNormalizer
{
    public function normalizeInlineReferences(
        File    $openApiFile,
        OpenApi $openApiDefinition
    ): ReferenceResolverResult
    {
        $refFileCollection = [];
        foreach ($openApiDefinition->paths as $path) {
            foreach ($path->getOperations() as $operation) {
                foreach ($operation->responses->getResponses() as $statusCode => $response) {
                    if ($response instanceof Reference) {
                        $operation->responses->addResponse(
                            $statusCode,
                            $this->normalizeReference($response, $refFileCollection)
                        );
                    }
                    if (!($response instanceof Response)) {
                        continue;
                    }

                    foreach ($response->content as $responseContent) {
                        /** @var $responseContent MediaType */
                        if ($responseContent->schema instanceof Reference) {
                            $responseContent->schema = $this->normalizeReference(
                                $responseContent->schema,
                                $refFileCollection
                            );
                        }
                        $newExamples = [];
                        foreach ($responseContent->examples as $key => $example) {
                            if ($example instanceof Reference) {
                                $newExamples[$key] = $this->normalizeReference(
                                    $example,
                                    $refFileCollection
                                );
                            } else {
                                $newExamples[$key] = $example;
                            }
                        }
                        if (count($newExamples) > 0) {
                            $responseContent->examples = $newExamples;
                        }
                    }
                }
            }
        }

        return new ReferenceResolverResult(
            $openApiDefinition,
            $this->normalizeFilePaths($openApiFile, $refFileCollection)
        );
    }

    /**
     * @param array<int, string> $refFileCollection
     */
    private function normalizeReference(?Reference $reference, array &$refFileCollection): ?Reference
    {
        if ($reference === null) {
            return null;
        }

        $matches       = [];
        $referenceFile = $reference->getReference();
        if (preg_match('~^(?<referenceFile>.*)(?<referenceString>#/.*)~', $referenceFile, $matches) === 1) {
            $refFile = $matches['referenceFile'];

            $refFileCollection[] = $refFile;

            return new Reference(['$ref' => $matches['referenceString']]);
        }

        return $reference;
    }

    /**
     * @param array<int, string> $refFileCollection
     *
     * @return array<int, File>
     */
    private function normalizeFilePaths(File $openApiFile, array $refFileCollection): array
    {
        return array_map(
            static fn(string $refFile): File => new File(
                $openApiFile->getAbsolutePath() . DIRECTORY_SEPARATOR . $refFile
            ),
            $refFileCollection
        );
    }
}

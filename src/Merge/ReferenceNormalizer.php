<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\Response;
use Mthole\OpenApiMerge\FileHandling\File;

use function array_map;
use function assert;
use function count;
use function preg_match;

use const DIRECTORY_SEPARATOR;

class ReferenceNormalizer
{
    public function normalizeInlineReferences(
        File $openApiFile,
        OpenApi $openApiDefinition
    ): ReferenceResolverResult {
        $refFileCollection = [];
        foreach ($openApiDefinition->paths as $path) {
            foreach ($path->getOperations() as $operation) {
                assert($operation->responses !== null);
                foreach ($operation->responses->getResponses() as $statusCode => $response) {
                    if ($response instanceof Reference) {
                        $operation->responses->addResponse(
                            $statusCode,
                            $this->normalizeReference($response, $refFileCollection)
                        );
                    }

                    if (! ($response instanceof Response)) {
                        continue;
                    }

                    foreach ($response->content as $responseContent) {
                        assert($responseContent instanceof MediaType);
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

                        if (count($newExamples) <= 0) {
                            continue;
                        }

                        $responseContent->examples = $newExamples;
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
     * @param list<string> $refFileCollection
     */
    private function normalizeReference(Reference $reference, array &$refFileCollection): Reference
    {
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
     * @param list<string> $refFileCollection
     *
     * @return list<File>
     */
    private function normalizeFilePaths(File $openApiFile, array $refFileCollection): array
    {
        return array_map(
            static fn (string $refFile): File => new File(
                $openApiFile->getAbsolutePath() . DIRECTORY_SEPARATOR . $refFile
            ),
            $refFileCollection
        );
    }
}

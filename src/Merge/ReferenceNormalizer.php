<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use Mthole\OpenApiMerge\FileHandling\File;
use openapiphp\openapi\spec\MediaType;
use openapiphp\openapi\spec\OpenApi;
use openapiphp\openapi\spec\Parameter;
use openapiphp\openapi\spec\Reference;
use openapiphp\openapi\spec\RequestBody;
use openapiphp\openapi\spec\Response;
use openapiphp\openapi\spec\Schema;

use function array_map;
use function array_values;
use function assert;
use function count;
use function preg_match;
use function sha1;

use const DIRECTORY_SEPARATOR;

class ReferenceNormalizer
{
    public function normalizeInlineReferences(
        File $openApiFile,
        OpenApi $openApiDefinition,
    ): ReferenceResolverResult {
        $refFileCollection = [];
        foreach ($openApiDefinition->paths as $path) {
            foreach ($path->parameters as $parameterIndex => $parameter) {
                $allParameters = $path->parameters;
                if ($parameter instanceof Reference) {
                    $allParameters[$parameterIndex] = $this->normalizeReference($parameter, $refFileCollection);
                } else {
                    $allParameters[$parameterIndex] = $this->normalizeParameters($parameter, $refFileCollection);
                }

                $path->parameters = $allParameters;
            }

            foreach ($path->getOperations() as $operation) {
                foreach (($operation->parameters ?? []) as $parameterIndex => $parameter) {
                    if (! $parameter instanceof Parameter) {
                        continue;
                    }

                    /** @var array<int, Parameter> $allParameters */
                    $allParameters                  = $operation->parameters;
                    $allParameters[$parameterIndex] = $this->normalizeParameters($parameter, $refFileCollection);

                    $operation->parameters = $allParameters;
                }

                if ($operation->requestBody instanceof RequestBody) {
                    foreach ($operation->requestBody->content as $contentType => $content) {
                        $allRequestBodyContent = $operation->requestBody->content;
                        if (! $content->schema instanceof Schema) {
                            continue;
                        }

                        $allRequestBodyContent[$contentType]->schema = $this->normalizeProperties(
                            $content->schema,
                            $refFileCollection,
                        );
                        $operation->requestBody->content             = $allRequestBodyContent;
                    }
                }

                assert($operation->responses !== null);
                foreach ($operation->responses->getResponses() as $statusCode => $response) {
                    if ($response instanceof Reference) {
                        $operation->responses->addResponse(
                            (string) $statusCode,
                            $this->normalizeReference($response, $refFileCollection),
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
                                $refFileCollection,
                            );
                        }

                        if ($responseContent->schema instanceof Schema) {
                            $schemaProperties = $responseContent->schema->properties ?? [];
                            foreach ($schemaProperties as $propertyName => $property) {
                                if (! ($property instanceof Reference)) {
                                    continue;
                                }

                                $schemaProperties[$propertyName] = $this->normalizeReference(
                                    $property,
                                    $refFileCollection,
                                );
                            }

                            if ($schemaProperties !== []) {
                                $responseContent->schema->properties = $schemaProperties;
                            }
                        }

                        $newExamples = [];
                        foreach ($responseContent->examples as $key => $example) {
                            if ($example instanceof Reference) {
                                $newExamples[$key] = $this->normalizeReference(
                                    $example,
                                    $refFileCollection,
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

        if ($openApiDefinition->components !== null) {
            foreach (($openApiDefinition->components->schemas ?? []) as $key => $schema) {
                $allSchemas = $openApiDefinition->components->schemas;
                if (! $schema instanceof Schema) {
                    continue;
                }

                $allSchemas[$key]                       = $this->normalizeProperties($schema, $refFileCollection);
                $openApiDefinition->components->schemas = $allSchemas;
            }

            foreach (($openApiDefinition->components->responses ?? []) as $key => $response) {
                if (! $response instanceof Response) {
                    continue;
                }

                /** @var array<string, Response> $allSchemas */
                $allSchemas  = $openApiDefinition->components->responses;
                $allContents = $allSchemas[$key]->content;
                foreach ($response->content as $contentKey => $content) {
                    if (! $content->schema instanceof Schema) {
                        continue;
                    }

                    $allContents[$contentKey]->schema = $this->normalizeProperties(
                        $content->schema,
                        $refFileCollection,
                    );
                }

                $allSchemas[$key]->content = $allContents;

                $openApiDefinition->components->responses = $allSchemas;
            }
        }

        return new ReferenceResolverResult(
            $openApiDefinition,
            $this->normalizeFilePaths($openApiFile, $refFileCollection),
        );
    }

    /** @param array<string, string> $refFileCollection */
    private function normalizeReference(Reference $reference, array &$refFileCollection): Reference
    {
        $matches       = [];
        $referenceFile = $reference->getReference();
        if (preg_match('~^(?<referenceFile>.*)(?<referenceString>#/.*)~', $referenceFile, $matches) === 1) {
            $refFile = $matches['referenceFile'];

            if ($refFile !== '') {
                $refFileCollection[sha1($refFile)] = $refFile;
            }

            return new Reference(['$ref' => $matches['referenceString']]);
        }

        return $reference;
    }

    /**
     * @param array<string, string> $refFileCollection
     *
     * @return list<File>
     */
    private function normalizeFilePaths(File $openApiFile, array $refFileCollection): array
    {
        return array_map(
            static function (string $refFile) use ($openApiFile): File {
                $absoluteFile = new File($refFile);
                if ($absoluteFile->exists()) {
                    return $absoluteFile;
                }

                return new File(
                    $openApiFile->getAbsolutePath() . DIRECTORY_SEPARATOR . $refFile,
                );
            },
            array_values($refFileCollection),
        );
    }

    /** @param array<string, string> $refFileCollection */
    public function normalizeProperties(Schema $schema, array &$refFileCollection): Schema
    {
        if (! isset($schema->properties)) {
            return $schema;
        }

        $schema        = $this->normalizeProperty($schema, $refFileCollection);
        $newProperties = $schema->properties;
        foreach ($schema->properties as $propertyName => $property) {
            $newProperties[$propertyName] = $this->normalizeProperty($property, $refFileCollection);
            $schema->properties           = $newProperties;
        }

        return $schema;
    }

    /**
     * @param TArg                  $property
     * @param array<string, string> $refFileCollection
     *
     * @phpstan-return TArg
     *
     * @template TArg of Reference|Schema
     */
    public function normalizeProperty(Reference|Schema $property, array &$refFileCollection): Reference|Schema
    {
        if ($property instanceof Reference) {
            return $this->normalizeReference($property, $refFileCollection);
        }

        if (! ($property instanceof Schema)) {
            return $property;
        }

        if ($property->items !== null) {
            $property->items = $this->normalizeProperty($property->items, $refFileCollection);
        }

        if ($property->additionalProperties instanceof Reference || $property->additionalProperties instanceof Schema) {
            $property->additionalProperties = $this->normalizeProperty(
                $property->additionalProperties,
                $refFileCollection,
            );
        }

        foreach (($property->anyOf ?? []) as $index => $anyOf) {
            $anyOfs          = $property->anyOf;
            $anyOfs[$index]  = $this->normalizeProperty($anyOf, $refFileCollection);
            $property->anyOf = $anyOfs;
        }

        foreach (($property->allOf ?? []) as $index => $allOf) {
            $allOfs          = $property->allOf;
            $allOfs[$index]  = $this->normalizeProperty($allOf, $refFileCollection);
            $property->allOf = $allOfs;
        }

        foreach (($property->oneOf ?? []) as $index => $oneOf) {
            $oneOfs          = $property->oneOf;
            $oneOfs[$index]  = $this->normalizeProperty($oneOf, $refFileCollection);
            $property->oneOf = $oneOfs;
        }

        return $property;
    }

    /** @param array<string, string> $refFileCollection */
    public function normalizeParameters(Parameter $parameter, array &$refFileCollection): Parameter
    {
        if ($parameter->schema instanceof Reference) {
            $parameter->schema = $this->normalizeReference(
                $parameter->schema,
                $refFileCollection,
            );
        }

        if ($parameter->schema instanceof Schema) {
            $parameter->schema = $this->normalizeProperties(
                $parameter->schema,
                $refFileCollection,
            );
        }

        return $parameter;
    }
}

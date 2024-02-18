<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use Mthole\OpenApiMerge\Util\Json;
use openapiphp\openapi\spec\Components;
use openapiphp\openapi\spec\OpenApi;

use function array_merge;
use function count;

class ComponentsMerger implements MergerInterface
{
    public function merge(
        OpenApi $existingSpec,
        OpenApi $newSpec,
    ): OpenApi {
        $mergedComponents   = new Components([]);
        $existingComponents = $existingSpec->components;
        $newComponents      = $newSpec->components;

        if (
            count($existingComponents->schemas ?? []) > 0
            || count($newComponents->schemas ?? []) > 0
        ) {
            $mergedComponents->schemas = array_merge(
                $existingComponents->schemas ?? [],
                $newComponents->schemas ?? [],
            );
        }

        if (
            count($existingComponents->securitySchemes ?? []) > 0
            || count($newComponents->securitySchemes ?? []) > 0
        ) {
            $mergedComponents->securitySchemes = array_merge(
                $existingComponents->securitySchemes ?? [],
                $newComponents->securitySchemes ?? [],
            );
        }

        if (
            count($existingComponents->requestBodies ?? []) > 0
            || count($newComponents->requestBodies ?? []) > 0
        ) {
            $mergedComponents->requestBodies = array_merge(
                $existingComponents->requestBodies ?? [],
                $newComponents->requestBodies ?? [],
            );
        }

        if (
            count($existingComponents->responses ?? []) > 0
            || count($newComponents->responses ?? []) > 0
        ) {
            $mergedComponents->responses = array_merge(
                $existingComponents->responses ?? [],
                $newComponents->responses ?? [],
            );
        }

        $clonedSpec = new OpenApi(Json::toArray($existingSpec->getSerializableData()));

        $clonedSpec->components = $mergedComponents;

        return $clonedSpec;
    }
}

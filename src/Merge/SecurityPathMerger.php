<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use cebe\openapi\spec\OpenApi;
use Mthole\OpenApiMerge\Util\Json;

use function count;

class SecurityPathMerger implements MergerInterface
{
    public function merge(
        OpenApi $existingSpec,
        OpenApi $newSpec,
    ): OpenApi {
        if (count($newSpec->security ?? []) === 0) {
            return $existingSpec;
        }

        $clonedSpec = new OpenApi(Json::toArray($existingSpec->getSerializableData()));

        foreach ($newSpec->paths->getPaths() as $pathName => $path) {
            foreach ($path->getOperations() as $method => $operation) {
                if ($operation->security !== null) {
                    continue;
                }

                $path = $clonedSpec->paths->getPath($pathName);
                if (! isset($path->{$method}) || $path->{$method} === null) {
                    continue;
                }

                $path->{$method}->security = $newSpec->security;
            }
        }

        return $clonedSpec;
    }
}

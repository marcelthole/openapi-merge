<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use openapiphp\openapi\spec\OpenApi;

use function count;

class SecurityPathMerger implements MergerInterface
{
    public function merge(
        OpenApi $mergedSpec,
        OpenApi $newSpec,
    ): void {
        if (count($newSpec->security ?? []) === 0) {
            return;
        }

        foreach ($newSpec->paths->getPaths() as $pathName => $path) {
            foreach ($path->getOperations() as $method => $operation) {
                if ($operation->security !== null) {
                    continue;
                }

                $path = $mergedSpec->paths->getPath($pathName);
                if (! isset($path->{$method}) || $path->{$method} === null) {
                    continue;
                }

                $path->{$method}->security = $newSpec->security;
            }
        }
    }
}

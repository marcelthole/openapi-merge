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
                // php-openapi 2 documents $security as non-nullable, but it is null when unset
                if ($operation->security !== null) { // @phpstan-ignore notIdentical.alwaysTrue
                    continue;
                }

                $path = $mergedSpec->paths->getPath($pathName); // @phpstan-ignore deadCode.unreachable
                if (! isset($path->{$method}) || $path->{$method} === null) {
                    continue;
                }

                $path->{$method}->security = $newSpec->security;
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use openapiphp\openapi\spec\OpenApi;
use openapiphp\openapi\spec\Paths;

class PathMerger implements MergerInterface
{
    private const MERGE_METHODS = [
        'get',
        'put',
        'post',
        'delete',
        'options',
        'head',
        'patch',
        'trace',
    ];

    public function merge(
        OpenApi $mergedSpec,
        OpenApi $newSpec,
    ): void {
        $existingPaths = $mergedSpec->paths;
        $newPaths      = $newSpec->paths;

        $pathCopy = new Paths($existingPaths->getPaths());
        foreach ($newPaths->getPaths() as $pathName => $newPath) {
            $existingPath = $pathCopy->getPath($pathName);

            if ($existingPath === null) {
                $pathCopy->addPath($pathName, $newPath);
                continue;
            }

            foreach (self::MERGE_METHODS as $method) {
                if ($existingPath->{$method} !== null) {
                    continue;
                }

                if ($newPath->{$method} === null) {
                    continue;
                }

                $existingPath->{$method} = $newPath->{$method};
            }
        }

        $mergedSpec->paths = $pathCopy;
    }
}

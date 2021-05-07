<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use cebe\openapi\spec\Paths;

class PathMerger implements PathMergerInterface
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

    public function mergePaths(Paths $existingPaths, Paths $newPaths): Paths
    {
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

        return $pathCopy;
    }
}

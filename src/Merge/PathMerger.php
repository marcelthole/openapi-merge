<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Paths;
use Mthole\OpenApiMerge\Util\Json;

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
        OpenApi $existingSpec,
        OpenApi $newSpec,
    ): OpenApi {
        $existingPaths = $existingSpec->paths;
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

        $clonedSpec = new OpenApi(Json::toArray($existingSpec->getSerializableData()));

        $clonedSpec->paths = $pathCopy;

        return $clonedSpec;
    }
}

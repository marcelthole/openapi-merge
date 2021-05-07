<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;

interface PathMergerInterface
{
    /**
     * @param Paths<PathItem> $existingPaths
     * @param Paths<PathItem> $newPaths
     *
     * @return Paths<PathItem>
     */
    public function mergePaths(Paths $existingPaths, Paths $newPaths): Paths;
}

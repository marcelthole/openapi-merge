<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Merge;

use openapiphp\openapi\spec\OpenApi;

interface MergerInterface
{
    public function merge(
        OpenApi $existingSpec,
        OpenApi $newSpec,
    ): OpenApi;
}

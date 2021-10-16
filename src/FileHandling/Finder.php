<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\FileHandling;

interface Finder
{
    /**
     * @return list<string>
     */
    public function find(string $baseDirectory, string $searchString): array;
}

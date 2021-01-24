<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Filesystem;

interface DirReaderInterface
{
    /**
     * @return string[]
     */
    public function getDirContents(string $dir): array;
}

<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Console\IO;

class PrintWriter implements WriterInterface
{
    public function write(string $message): void
    {
        echo $message;
    }
}

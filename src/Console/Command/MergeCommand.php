<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Console\Command;

use Mthole\OpenApiMerge\Console\IO\WriterInterface;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\OpenApiMergeInterface;
use Mthole\OpenApiMerge\Writer\DefinitionWriterInterface;

final class MergeCommand implements CommandInterface
{
    private OpenApiMergeInterface $merger;
    private DefinitionWriterInterface $definitionWriter;

    public function __construct(
        OpenApiMergeInterface $openApiMerge,
        DefinitionWriterInterface $definitionWriter
    ) {
        $this->merger           = $openApiMerge;
        $this->definitionWriter = $definitionWriter;
    }

    /**
     * @param File[] $additionalFiles
     */
    public function run(
        WriterInterface $io,
        File $baseFile,
        array $additionalFiles
    ): void {
        $mergedResult = $this->merger->mergeFiles(
            $baseFile,
            ...$additionalFiles
        );

        $io->write(
            $this->definitionWriter->write($mergedResult)
        );
    }
}

<?php

declare(strict_types=1);

namespace OpenApiMerge\Console\Command;

use OpenApiMerge\Console\IO\WriterInterface;
use OpenApiMerge\FileHandling\File;
use OpenApiMerge\OpenApiMergeInterface;
use OpenApiMerge\Writer\DefinitionWriterInterface;

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

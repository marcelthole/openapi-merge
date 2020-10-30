<?php

declare(strict_types=1);

namespace OpenApiMerge\Console;

use OpenApiMerge\Console\Command\CommandInterface;
use OpenApiMerge\Console\IO\PrintWriter;
use OpenApiMerge\Console\IO\WriterInterface;
use OpenApiMerge\FileHandling\File;

use function array_map;
use function array_slice;
use function basename;
use function in_array;

class Application
{
    private WriterInterface $io;

    private CommandInterface $command;

    public function __construct(
        CommandInterface $command,
        ?WriterInterface $io = null
    ) {
        $this->io      = $io ?? new PrintWriter();
        $this->command = $command;
    }

    /**
     * @param array<int, string> $argv
     */
    public function run(array $argv): int
    {
        $cmd             = basename($argv[0]);
        $hasHelpArgument = in_array('-h', $argv, true) || in_array('--help', $argv, true);
        if ($hasHelpArgument) {
            $this->printUsage($cmd);

            return 0;
        }

        $baseFile        = $argv[1] ?? false;
        $additionalFiles = array_slice($argv, 2);

        if (! $baseFile || empty($additionalFiles)) {
            $this->io->write(
                <<<'ERR'
            Error:
                Basefile or additional files are missing!
            
            ERR
            );

            return 1;
        }

        $this->command->run(
            $this->io,
            new File($baseFile),
            array_map(
                static fn (string $file): File => new File($file),
                $additionalFiles
            )
        );

        return 0;
    }

    private function printUsage(string $cmd): void
    {
        $this->io->write(
            <<<USAGE
        Usage:
            $cmd basefile.yml additionalFileA.yml additionalFileB.yml additionalFileC.yml [...]  > combined.yml
            
        Allowed extensions:
            Only .yml, .yaml and .json files are supported
        
        Outputformat:
            The output format is determined by the basefile extension.

        USAGE
        );
    }
}

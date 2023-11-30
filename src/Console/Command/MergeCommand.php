<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Console\Command;

use Exception;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\Finder;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Filesystem\DirReader;
use Mthole\OpenApiMerge\Filesystem\DirReaderInterface;
use Mthole\OpenApiMerge\OpenApiMergeInterface;
use Mthole\OpenApiMerge\Writer\DefinitionWriterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function array_filter;
use function array_map;
use function array_merge;
use function array_unique;
use function count;
use function file_put_contents;
use function is_array;
use function is_string;
use function sprintf;
use function touch;

final class MergeCommand extends Command
{
    public const COMMAND_NAME = 'openapi:merge';

    public function __construct(
        private OpenApiMergeInterface $merger,
        private DefinitionWriterInterface $definitionWriter,
        private Finder $fileFinder,
        private DirReaderInterface|null $dirReader = null,
    ) {
        parent::__construct(self::COMMAND_NAME);

        $this->dirReader = $dirReader ?? new DirReader();
    }

    protected function configure(): void
    {
        $this->setDescription('Merge multiple OpenAPI definition files into a single file')
            ->setHelp(
                <<<'HELP'
                Usage:
                basefile.yml additionalFileA.yml additionalFileB.yml [...] > combined.yml
                basefile.yml additionalFileA.yml --dir /var/www/docs/source1 --dir /var/www/docs/source2 > combined.yml

                Allowed extensions:
                    Only .yml, .yaml and .json files are supported

                Outputformat:
                    The output format is determined by the basefile extension.
                HELP,
            )
            ->addArgument('basefile', InputArgument::REQUIRED)
            ->addArgument('additionalFiles', InputArgument::IS_ARRAY | InputArgument::OPTIONAL)
            ->addOption(
                'dir',
                'd',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'A dir to scan for additional files',
            )
            ->addOption(
                'match',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                <<<'DESCRIPTION'
                Use a RegEx pattern to determine the additionalFiles.
                If this option is set the additionalFiles could be omitted
                DESCRIPTION,
            )
            ->addOption(
                'resolve-references',
                null,
                InputOption::VALUE_OPTIONAL,
                'Resolve the "$refs" in the given files',
                true,
            )
            ->addOption(
                'outputfile',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Defines the output file for the result. Defaults the result will printed to stdout',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseFile        = $input->getArgument('basefile');
        $additionalFiles = $input->getArgument('additionalFiles');

        if (
            ! is_array($additionalFiles) ||
            array_filter($additionalFiles, static fn (mixed $input): bool => is_string($input)) !== $additionalFiles
        ) {
            throw new Exception('Invalid arguments given');
        }

        if (count($additionalFiles) === 0) {
            $matches = $input->getOption('match');
            if (
                ! is_array($matches) ||
                array_filter($matches, static fn (mixed $input): bool => is_string($input)) !== $matches
            ) {
                throw new Exception('Invalid arguments given');
            }

            foreach ($matches as $regex) {
                $additionalFiles = [...$additionalFiles, ...$this->fileFinder->find('.', $regex)];
            }
        }

        if ($input->hasOption('dir')) {
            $additionalFiles = (array) ($additionalFiles ?? []);
            /** @var string[] $dirs */
            $dirs = array_unique((array) $input->getOption('dir'));

            foreach ($dirs as $dir) {
                $additionalFiles = array_merge($additionalFiles, $this->dirReader->getDirContents((string) $dir)); //@phpstan-ignore-line
            }
        }

        if (! is_string($baseFile) || count($additionalFiles) === 0) {
            throw new Exception('Invalid arguments given');
        }

        $shouldResolveReferences = (bool) $input->getOption('resolve-references');

        $mergedResult = $this->merger->mergeFiles(
            new File($baseFile),
            array_map(
                static fn (string $file): File => new File($file),
                $additionalFiles,
            ),
            $shouldResolveReferences,
        );

        $outputFileName = $input->getOption('outputfile');
        if (is_string($outputFileName)) {
            touch($outputFileName);
            $outputFile        = new File($outputFileName);
            $specificationFile = new SpecificationFile(
                $outputFile,
                $mergedResult->getOpenApi(),
            );
            file_put_contents(
                $outputFile->getAbsoluteFile(),
                $this->definitionWriter->write($specificationFile),
            );
            $output->writeln(sprintf('File successfully written to %s', $outputFile->getAbsoluteFile()));
        } else {
            $output->write($this->definitionWriter->write($mergedResult));
        }

        return Command::SUCCESS;
    }
}

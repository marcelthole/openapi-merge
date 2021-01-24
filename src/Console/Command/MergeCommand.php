<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Console\Command;

use Exception;
use Mthole\OpenApiMerge\Config\Config;
use Mthole\OpenApiMerge\Config\ConfigAwareInterface;
use Mthole\OpenApiMerge\FileHandling\File;
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

use function array_map;
use function array_merge;
use function array_unique;
use function file_put_contents;
use function is_array;
use function is_string;
use function sprintf;
use function touch;

final class MergeCommand extends Command
{
    public const COMMAND_NAME = 'openapi:merge';

    private OpenApiMergeInterface $merger;
    private DefinitionWriterInterface $definitionWriter;
    private DirReaderInterface $dirReader;

    public function __construct(
        OpenApiMergeInterface $openApiMerge,
        DefinitionWriterInterface $definitionWriter,
        ?DirReaderInterface $dirReader = null
    ) {
        parent::__construct(self::COMMAND_NAME);
        $this->merger           = $openApiMerge;
        $this->definitionWriter = $definitionWriter;
        $this->dirReader        = $dirReader ?? new DirReader();
    }

    protected function configure(): void
    {
        $this->setDescription('Merge multiple OpenAPI definition files into a single file')
            ->setHelp(<<<'HELP'
                Usage:
                basefile.yml additionalFileA.yml additionalFileB.yml [...] > combined.yml
                basefile.yml additionalFileA.yml --dir /var/www/docs/source1 --dir /var/www/docs/source2 > combined.yml

                Allowed extensions:
                    Only .yml, .yaml and .json files are supported

                Outputformat:
                    The output format is determined by the basefile extension.
                HELP
            )
            ->addArgument('basefile', InputArgument::REQUIRED)
            ->addArgument('additionalFiles', InputArgument::IS_ARRAY)
            ->addOption(
                'dir',
                'd',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL,
                'A dir to scan for additional files'
            )
            ->addOption(
                'skip-resolving-references',
                null,
                InputOption::VALUE_NONE,
                'An option to skip resolving of references'
            )
            ->addOption(
                'reset-components',
                null,
                InputOption::VALUE_OPTIONAL,
                'An option to reset component in the schema',
                true
            )
            ->addOption(
                'outputfile',
                'o',
                InputOption::VALUE_OPTIONAL,
                'Defines the output file for the result. Defaults the result will printed to stdout'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseFile        = $input->getArgument('basefile');
        $additionalFiles = $input->getArgument('additionalFiles');

        if ($input->hasOption('dir')) {
            $additionalFiles = (array) ($additionalFiles ?? []);
            $dirs            = array_unique((array) $input->getOption('dir'));

            foreach ($dirs as $dir) {
                $additionalFiles = array_merge($additionalFiles, $this->dirReader->getDirContents((string) $dir));
            }
        }

        if (! is_string($baseFile) || ! is_array($additionalFiles)) {
            throw new Exception('Invalid arguments given');
        }

        if ($this->merger instanceof ConfigAwareInterface) {
            $config = (new Config())
                ->resetComponents($input->getOption('reset-components') !== 'false')
                ->skipResolvingReferences((bool) $input->getOption('skip-resolving-references'));

            $this->merger->setConfig($config);
        }

        $mergedResult = $this->merger->mergeFiles(
            new File($baseFile),
            ...array_map(
                static fn (string $file): File => new File($file),
                $additionalFiles
            )
        );

        $outputFileName = $input->getOption('outputfile');
        if (is_string($outputFileName)) {
            touch($outputFileName);
            $outputFile        = new File($outputFileName);
            $specificationFile = new SpecificationFile(
                $outputFile,
                $mergedResult->getOpenApi()
            );
            file_put_contents(
                $outputFile->getAbsolutePath(),
                $this->definitionWriter->write($specificationFile)
            );
            $output->writeln(sprintf('File successfully written to %s', $outputFile->getAbsolutePath()));
        } else {
            $output->write($this->definitionWriter->write($mergedResult));
        }

        return Command::SUCCESS;
    }
}

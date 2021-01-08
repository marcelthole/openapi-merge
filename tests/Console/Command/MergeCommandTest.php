<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Console\Command;

use cebe\openapi\spec\OpenApi;
use Generator;
use Mthole\OpenApiMerge\Console\Command\MergeCommand;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\OpenApiMergeInterface;
use Mthole\OpenApiMerge\Writer\DefinitionWriterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\TrimmedBufferOutput;
use Throwable;

use function sprintf;
use function sys_get_temp_dir;
use function unlink;

use const PHP_EOL;

/**
 * @uses \Mthole\OpenApiMerge\FileHandling\File
 * @uses \Mthole\OpenApiMerge\FileHandling\SpecificationFile
 *
 * @covers \Mthole\OpenApiMerge\Console\Command\MergeCommand
 */
class MergeCommandTest extends TestCase
{
    /**
     * @dataProvider invalidArgumentsDataProvider
     */
    public function testRunWithInvalidArguments(ArrayInput $input): void
    {
        $sut = new MergeCommand(
            $this->createStub(OpenApiMergeInterface::class),
            $this->createStub(DefinitionWriterInterface::class)
        );

        $output = new TrimmedBufferOutput(1024);

        self::expectException(Throwable::class);
        $sut->run($input, $output);
    }

    /**
     * @return Generator<array<int, ArrayInput>>
     */
    public function invalidArgumentsDataProvider(): Generator
    {
        yield [
            new ArrayInput([
                'basefile' => null,
                'additionalFiles' => ['secondfile.yml'],
            ]),
        ];

        yield [
            new ArrayInput([
                'basefile' => 'basefile.yml',
                'additionalFiles' => 'secondfile.yml',
            ]),
        ];

        yield [
            new ArrayInput([
                'basefile' => null,
                'additionalFiles' => 'secondfile.yml',
            ]),
        ];
    }

    public function testRun(): void
    {
        $baseFile   = new File('basefile.yml');
        $secondFile = new File('secondfile.yml');

        $mergeResultStub = new SpecificationFile(
            new File('dummy'),
            $this->createStub(OpenApi::class)
        );

        $mergeMock = $this->createMock(OpenApiMergeInterface::class);
        $mergeMock->expects(self::once())
            ->method('mergeFiles')
            ->with($baseFile, $secondFile)
            ->willReturn($mergeResultStub);

        $definitionWriterMock = $this->createMock(DefinitionWriterInterface::class);
        $definitionWriterMock->expects(self::once())
            ->method('write')
            ->with($mergeResultStub)
            ->willReturn('dummy-write');

        $sut = new MergeCommand(
            $mergeMock,
            $definitionWriterMock
        );

        $input  = new ArrayInput([
            'basefile' => 'basefile.yml',
            'additionalFiles' => ['secondfile.yml'],
        ]);
        $output = new TrimmedBufferOutput(1024);
        self::assertEquals(0, $sut->run($input, $output));

        self::assertSame('dummy-write', $output->fetch());
    }

    public function testRunWriteToFile(): void
    {
        $definitionWriterMock  = new class implements DefinitionWriterInterface {
            public function write(SpecificationFile $specFile): string
            {
                return 'dummy-data';
            }
        };
        $openApiMergeInterface = new class implements OpenApiMergeInterface {
            public function mergeFiles(File $baseFile, File ...$additionalFiles): SpecificationFile
            {
                return new SpecificationFile(
                    new File('dummy'),
                    new OpenApi([])
                );
            }
        };

        $sut = new MergeCommand(
            $openApiMergeInterface,
            $definitionWriterMock
        );

        $tmpFile = sys_get_temp_dir() . '/merge-result.json';
        try {
            $input  = new ArrayInput([
                'basefile'        => 'basefile.yml',
                'additionalFiles' => ['secondfile.yml'],
                '-o'              => $tmpFile,
            ]);
            $output = new TrimmedBufferOutput(1024);
            self::assertEquals(0, $sut->run($input, $output));

            self::assertSame(
                sprintf('File successfully written to %s%s', $tmpFile, PHP_EOL),
                $output->fetch()
            );
            self::assertStringEqualsFile($tmpFile, 'dummy-data');
        } finally {
            @unlink($tmpFile);
        }
    }
}

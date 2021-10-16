<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Console\Command;

use cebe\openapi\spec\OpenApi;
use Generator;
use Mthole\OpenApiMerge\Console\Command\MergeCommand;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\Finder;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\OpenApiMergeInterface;
use Mthole\OpenApiMerge\Writer\DefinitionWriterInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\TrimmedBufferOutput;

use function array_merge;
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
            $this->createStub(DefinitionWriterInterface::class),
            $this->createStub(Finder::class)
        );

        $output = new TrimmedBufferOutput(1024);

        self::expectExceptionMessage('Invalid arguments given');
        $sut->run($input, $output);
    }

    /**
     * @return Generator<list<ArrayInput>>
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
                'additionalFiles' => [],
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
            ->with($baseFile, [$secondFile])
            ->willReturn($mergeResultStub);

        $definitionWriterMock = $this->createMock(DefinitionWriterInterface::class);
        $definitionWriterMock->expects(self::once())
            ->method('write')
            ->with($mergeResultStub)
            ->willReturn('dummy-write');

        $sut = new MergeCommand(
            $mergeMock,
            $definitionWriterMock,
            $this->createStub(Finder::class)
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
            /** @param list<File> $additionalFiles */
            public function mergeFiles(
                File $baseFile,
                array $additionalFiles,
                bool $resolveReference = true
            ): SpecificationFile {
                return new SpecificationFile(
                    new File('dummy'),
                    new OpenApi([])
                );
            }
        };

        $sut = new MergeCommand(
            $openApiMergeInterface,
            $definitionWriterMock,
            $this->createStub(Finder::class)
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

    /**
     * @return array<string, list<mixed>>
     */
    public function resolveReferenceArgumentDataProvider(): iterable
    {
        yield 'default-param' => [null, true];
        yield 'one as string' => ['1', true];
        yield 'zero as string' => ['0', false];
        yield 'true' => [true, true];
        yield 'false' => [false, false];
    }

    /**
     * @param string|bool|null $resolveReferenceValue
     *
     * @dataProvider resolveReferenceArgumentDataProvider
     */
    public function testResolveReferencesArgument(
        $resolveReferenceValue,
        bool $expectedResolveReferenceValue
    ): void {
        $basefile              = 'basefile.yml';
        $additionalFile        = 'secondfile.yml';
        $definitionWriterMock  = new class implements DefinitionWriterInterface {
            public function write(SpecificationFile $specFile): string
            {
                return 'dummy-data';
            }
        };
        $openApiMergeInterface = $this->createMock(OpenApiMergeInterface::class);
        $openApiMergeInterface->method('mergeFiles')->with(
            new File($basefile),
            [new File($additionalFile)],
            $expectedResolveReferenceValue
        )->willReturn(new SpecificationFile(
            new File($basefile),
            new OpenApi([])
        ));

        $sut = new MergeCommand(
            $openApiMergeInterface,
            $definitionWriterMock,
            $this->createStub(Finder::class)
        );

        $arguments = [
            'basefile'             => $basefile,
            'additionalFiles'      => [$additionalFile],
        ];

        if ($resolveReferenceValue !== null) {
            $arguments['--resolve-references'] = $resolveReferenceValue;
        }

        $input  = new ArrayInput($arguments);
        $output = new TrimmedBufferOutput(1024);
        self::assertEquals(0, $sut->run($input, $output));
    }

    /**
     * @param array<string, list<string>> $arguments
     * @param list<File>                  $expectedFiles
     *
     * @dataProvider matchArgumentDataProvider
     */
    public function testMatchArgument(array $arguments, array $expectedFiles): void
    {
        $basefile = 'basefile.yml';

        $openApiMergeInterface = $this->createMock(OpenApiMergeInterface::class);
        $openApiMergeInterface->method('mergeFiles')->with(
            new File($basefile),
            $expectedFiles
        )->willReturn(new SpecificationFile(
            new File($basefile),
            new OpenApi([])
        ));

        $sut    = new MergeCommand(
            $openApiMergeInterface,
            $this->createStub(DefinitionWriterInterface::class),
            new class implements Finder {
                /** @return list<string> */
                public function find(string $baseDirectory, string $searchString): array
                {
                    return ['A.yml', 'B.yml'];
                }
            }
        );
        $input  = new ArrayInput(array_merge(['basefile' => $basefile], $arguments));
        $output = new TrimmedBufferOutput(1024);
        self::assertEquals(0, $sut->run($input, $output));
    }

    /**
     * @return iterable<string, array<string, mixed>>
     */
    public function matchArgumentDataProvider(): iterable
    {
        yield 'given additional files with match should ignore match' => [
            'arguments' => [
                'additionalFiles' => ['secondfile.yml'],
                '--match' => ['.*'],
            ],
            'expectedFiles' => [new File('secondfile.yml')],
        ];

        yield 'missing additionalFiles files with match should return match' => [
            'arguments' => [
                'additionalFiles' => [],
                '--match' => ['.*'],
            ],
            'expectedFiles' => [
                new File('A.yml'),
                new File('B.yml'),
            ],
        ];

        yield 'multiple matches return each match' => [
            'arguments' => [
                'additionalFiles' => [],
                '--match' => ['.*', '.*'],
            ],
            'expectedFiles' => [
                new File('A.yml'),
                new File('B.yml'),
                new File('A.yml'),
                new File('B.yml'),
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace OpenApiMerge\Tests\Console\Command;

use cebe\openapi\spec\OpenApi;
use OpenApiMerge\Console\Command\MergeCommand;
use OpenApiMerge\Console\IO\DummyWriter;
use OpenApiMerge\FileHandling\File;
use OpenApiMerge\FileHandling\SpecificationFile;
use OpenApiMerge\OpenApiMergeInterface;
use OpenApiMerge\Writer\DefinitionWriterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenApiMerge\Console\Command\MergeCommand
 */
class MergeCommandTest extends TestCase
{
    public function testRun(): void
    {
        $baseFile   = new File('basefile');
        $secondFile = new File('secondfile');

        $mergeResultStub = new SpecificationFile(
            new File('dummy'),
            $this->createStub(OpenApi::class)
        );

        $io = new DummyWriter();

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
        $sut->run(
            $io,
            $baseFile,
            [$secondFile]
        );

        self::assertSame(['dummy-write'], $io->getMessages());
    }
}

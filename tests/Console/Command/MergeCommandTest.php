<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Console\Command;

use cebe\openapi\spec\OpenApi;
use Mthole\OpenApiMerge\Console\Command\MergeCommand;
use Mthole\OpenApiMerge\Console\IO\DummyWriter;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\OpenApiMergeInterface;
use Mthole\OpenApiMerge\Writer\DefinitionWriterInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mthole\OpenApiMerge\Console\Command\MergeCommand
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

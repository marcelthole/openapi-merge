<?php

declare(strict_types=1);

namespace OpenApiMerge\Tests\Console;

use Generator;
use OpenApiMerge\Console\Application;
use OpenApiMerge\Console\Command\CommandInterface;
use OpenApiMerge\Console\IO\DummyWriter;
use OpenApiMerge\FileHandling\File;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenApiMerge\Console\Application
 */
class ApplicationTest extends TestCase
{
    /**
     * @dataProvider printsHelpDataProvider
     */
    public function testPrintsHelp(string $argument): void
    {
        $dummyWriter = new DummyWriter();
        $application = new Application($this->createStub(CommandInterface::class), $dummyWriter);
        $exitCode    = $application->run(['../binary.php', $argument]);

        self::assertSame(0, $exitCode);
        self::assertCount(1, $dummyWriter->getMessages());
        self::assertStringStartsWith('Usage:', $dummyWriter->getMessages()[0]);
        self::assertStringContainsString(' binary.php basefile.yml', $dummyWriter->getMessages()[0]);
    }

    /** @return Generator<array<int, string>> */
    public function printsHelpDataProvider(): Generator
    {
        yield ['-h'];
        yield ['--help'];
    }

    /**
     * @dataProvider wrongArgumentsDataProvider
     */
    public function testWrongUsage(string $argument): void
    {
        $dummyWriter = new DummyWriter();
        $application = new Application($this->createStub(CommandInterface::class), $dummyWriter);

        $exitCode = $application->run(['binary', $argument]);

        self::assertSame(1, $exitCode);
        self::assertCount(1, $dummyWriter->getMessages());
        self::assertStringStartsWith('Error:', $dummyWriter->getMessages()[0]);
    }

    /** @return Generator<array<int, string>> */
    public function wrongArgumentsDataProvider(): Generator
    {
        yield [''];
        yield ['singlefile-only.yml'];
    }

    public function testDelegateCommand(): void
    {
        $dummyWriter = new DummyWriter();
        $command     = $this->createMock(CommandInterface::class);
        $command->expects(self::once())->method('run')->with(
            $dummyWriter,
            new File('basefile.yml'),
            [
                new File('second-file.yml'),
                new File('third-file.yml'),
            ]
        );

        $application = new Application($command, $dummyWriter);
        self::assertSame(
            0,
            $application->run([
                'binary.php',
                'basefile.yml',
                'second-file.yml',
                'third-file.yml',
            ])
        );
    }
}

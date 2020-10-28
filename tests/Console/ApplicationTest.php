<?php

declare(strict_types=1);

namespace OpenApiMerge\Tests\Console;

use Generator;
use OpenApiMerge\Console\Application;
use OpenApiMerge\Console\IO\DummyWriter;
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
        $application = new Application($dummyWriter);
        $exitCode    = $application->run(['binary', $argument]);

        self::assertSame(0, $exitCode);
        self::assertCount(1, $dummyWriter->getMessages());
        self::assertStringStartsWith('Usage:', $dummyWriter->getMessages()[0]);
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
        $application = new Application($dummyWriter);

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
}

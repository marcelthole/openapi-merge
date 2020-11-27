<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\FileHandling;

use Generator;
use Mthole\OpenApiMerge\FileHandling\Exception\IOException;
use Mthole\OpenApiMerge\FileHandling\File;
use PHPUnit\Framework\TestCase;

use function getcwd;
use function str_replace;

/**
 * @covers \Mthole\OpenApiMerge\FileHandling\File
 */
class FileTest extends TestCase
{
    /**
     * @dataProvider fileExtensionProvider
     */
    public function testGetFileExtension(string $filename, string $expectedExtension): void
    {
        $sut = new File($filename);
        self::assertSame($expectedExtension, $sut->getFileExtension());
    }

    /** @return Generator<array<int, string>> */
    public function fileExtensionProvider(): Generator
    {
        yield ['base.yml', 'yml'];
        yield ['base.yaml', 'yaml'];
        yield ['base.json', 'json'];
        yield ['base.v2.json', 'json'];
        yield ['no-extension', ''];
        yield ['./../file.dat', 'dat'];
    }

    public function testGetAbsolutePathWithRelativeInvalidFile(): void
    {
        $sut = new File('dummyfile');

        $this->expectException(IOException::class);
        $this->expectExceptionMessageMatches('~/dummyfile~');

        $sut->getAbsolutePath();
    }

    public function testGetAbsolutePathWithAbsoluteInvalidFile(): void
    {
        $invalidFilename = __FILE__ . '-nonexisting.dat';
        $sut             = new File($invalidFilename);

        $this->expectException(IOException::class);
        $this->expectExceptionMessageMatches('~' . $invalidFilename . '~');

        $sut->getAbsolutePath();
    }

    public function testGetAbsolutePath(): void
    {
        $filename = str_replace(
            getcwd() ?: '',
            '.',
            __FILE__
        );

        self::assertNotSame(
            __FILE__,
            $filename
        );

        $sut = new File($filename);
        self::assertSame(
            __FILE__,
            $sut->getAbsolutePath()
        );
    }
}

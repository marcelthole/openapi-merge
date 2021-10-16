<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\FileHandling;

use Mthole\OpenApiMerge\FileHandling\Exception\IOException;
use Mthole\OpenApiMerge\FileHandling\File;
use PHPUnit\Framework\TestCase;

use function getcwd;
use function preg_quote;
use function str_replace;

/**
 * @uses   \Mthole\OpenApiMerge\FileHandling\Exception\IOException
 *
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

    /** @return list<list<string>> */
    public function fileExtensionProvider(): iterable
    {
        yield ['base.yml', 'yml'];
        yield ['base.yaml', 'yaml'];
        yield ['base.json', 'json'];
        yield ['base.v2.json', 'json'];
        yield ['no-extension', ''];
        yield ['./../file.dat', 'dat'];
    }

    public function testGetAbsoluteFileWithRelativeInvalidFile(): void
    {
        $sut = new File('dummyfile');

        $this->expectException(IOException::class);
        $this->expectExceptionMessageMatches('~\w+/dummyfile"~');

        $sut->getAbsoluteFile();
    }

    public function testGetAbsoluteFileWithAbsoluteInvalidFile(): void
    {
        $invalidFilename = __FILE__ . '-nonexisting.dat';
        $sut             = new File($invalidFilename);

        $this->expectException(IOException::class);
        $this->expectExceptionMessageMatches('~"' . preg_quote($invalidFilename, '~') . '"~');

        $sut->getAbsoluteFile();
    }

    public function testGetAbsoluteFile(): void
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
            $sut->getAbsoluteFile()
        );
    }

    public function testGetAbsolutePath(): void
    {
        $sut = new File(__FILE__);
        self::assertSame(__DIR__, $sut->getAbsolutePath());
    }
}

<?php

declare(strict_types=1);

namespace OpenApiMerge\Tests\FileHandling;

use Generator;
use OpenApiMerge\FileHandling\File;
use PHPUnit\Framework\TestCase;

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

    /**
     * @return iterable<int, array<int, string>>
     */
    public function fileExtensionProvider(): Generator
    {
        yield ['base.yml', 'yml'];
        yield ['base.yaml', 'yaml'];
        yield ['base.json', 'json'];
        yield ['base.v2.json', 'json'];
        yield ['no-extension', ''];
        yield ['./../file.dat', 'dat'];
    }
}

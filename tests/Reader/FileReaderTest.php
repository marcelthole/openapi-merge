<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Reader;

use cebe\openapi\spec\OpenApi;
use Generator;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\Reader\Exception\InvalidFileTypeException;
use Mthole\OpenApiMerge\Reader\FileReader;
use Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper;
use PHPUnit\Framework\TestCase;

/**
 * @uses   \Mthole\OpenApiMerge\FileHandling\File
 * @uses   \Mthole\OpenApiMerge\FileHandling\SpecificationFile
 * @uses   \Mthole\OpenApiMerge\Reader\Exception\InvalidFileTypeException
 * @uses   \Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper
 *
 * @covers \Mthole\OpenApiMerge\Reader\FileReader
 */
class FileReaderTest extends TestCase
{
    /**
     * @dataProvider validFilesDataProvider
     */
    public function testValidFiles(string $filename): void
    {
        $file          = new File($filename);
        $sut           = new FileReader();
        $specification = $sut->readFile($file);

        self::assertSame($file, $specification->getFile());
    }

    /** @return Generator<string[]> */
    public function validFilesDataProvider(): Generator
    {
        yield [__DIR__ . '/Fixtures/valid-openapi.yml'];
        yield [__DIR__ . '/Fixtures/valid-openapi.yaml'];
        yield [__DIR__ . '/Fixtures/valid-openapi.json'];
    }

    public function testInvalidFile(): void
    {
        $sut  = new FileReader();
        $file = new File('openapi.neon');

        $this->expectException(InvalidFileTypeException::class);
        $sut->readFile($file);
    }

    public function testPassResolveReference(): void
    {
        $dummyJsonFile = __DIR__ . '/Fixtures/valid-openapi.json';
        $dummyYamlFile = __DIR__ . '/Fixtures/valid-openapi.yml';

        $readerMock = $this->createMock(OpenApiReaderWrapper::class);
        $readerMock->expects(self::exactly(3))->method('readFromJsonFile')->withConsecutive(
            [$dummyJsonFile, OpenApi::class, true],
            [$dummyJsonFile, OpenApi::class, true],
            [$dummyJsonFile, OpenApi::class, false],
        )->willReturn(new OpenApi([]));

        $readerMock->expects(self::exactly(3))->method('readFromYamlFile')->withConsecutive(
            [$dummyYamlFile, OpenApi::class, true],
            [$dummyYamlFile, OpenApi::class, true],
            [$dummyYamlFile, OpenApi::class, false],
        )->willReturn(new OpenApi([]));

        $sut = new FileReader($readerMock);

        $sut->readFile(new File($dummyJsonFile));
        $sut->readFile(new File($dummyJsonFile), true);
        $sut->readFile(new File($dummyJsonFile), false);
        $sut->readFile(new File($dummyYamlFile));
        $sut->readFile(new File($dummyYamlFile), true);
        $sut->readFile(new File($dummyYamlFile), false);
    }
}

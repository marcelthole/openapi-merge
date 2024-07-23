<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Reader;

use Generator;
use InvalidArgumentException;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\Reader\Exception\InvalidFileTypeException;
use Mthole\OpenApiMerge\Reader\FileReader;
use Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper;
use openapiphp\openapi\spec\OpenApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileReader::class)]
#[UsesClass('\Mthole\OpenApiMerge\FileHandling\File')]
#[UsesClass('\Mthole\OpenApiMerge\FileHandling\SpecificationFile')]
#[UsesClass('\Mthole\OpenApiMerge\Reader\Exception\InvalidFileTypeException')]
#[UsesClass('\Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper')]
class FileReaderTest extends TestCase
{
    #[DataProvider('validFilesDataProvider')]
    public function testValidFiles(string $filename): void
    {
        $file          = new File($filename);
        $sut           = new FileReader();
        $specification = $sut->readFile($file);

        self::assertSame($file, $specification->getFile());
    }

    /** @return Generator<string[]> */
    public static function validFilesDataProvider(): Generator
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
        $matcher    = self::exactly(3);
        $readerMock->expects($matcher)
            ->method('readFromJsonFile')
            ->willReturnCallback(
                static function () use ($matcher, $dummyJsonFile) {
                    return match ($matcher->numberOfInvocations()) {
                        1 => [$dummyJsonFile, OpenApi::class, true],
                        2 => [$dummyJsonFile, OpenApi::class, true],
                        3 => [$dummyJsonFile, OpenApi::class, false],
                        default => throw new InvalidArgumentException('Did not expect more calls')
                    };
                },
            )->willReturn(new OpenApi([]));
        $matcher = self::exactly(3);

        $readerMock->expects($matcher)
            ->method('readFromYamlFile')
            ->willReturnCallback(
                static function () use ($matcher, $dummyYamlFile) {
                    return match ($matcher->numberOfInvocations()) {
                        1 => [$dummyYamlFile, OpenApi::class, true],
                        2 => [$dummyYamlFile, OpenApi::class, true],
                        3 => [$dummyYamlFile, OpenApi::class, false],
                        default => throw new InvalidArgumentException('Did not expect more calls')
                    };
                },
            )->willReturn(new OpenApi([]));

        $sut = new FileReader($readerMock);

        $sut->readFile(new File($dummyJsonFile));
        $sut->readFile(new File($dummyJsonFile), true);
        $sut->readFile(new File($dummyJsonFile), false);
        $sut->readFile(new File($dummyYamlFile));
        $sut->readFile(new File($dummyYamlFile), true);
        $sut->readFile(new File($dummyYamlFile), false);
    }

    public function testDefaultParam(): void
    {
        $dummyJsonFile = __DIR__ . '/Fixtures/valid-openapi.json';

        $readerMock = $this->createMock(OpenApiReaderWrapper::class);
        $readerMock->expects(self::once())->method('readFromJsonFile')->with(
            $dummyJsonFile,
            OpenApi::class,
            true,
        )->willReturn(new OpenApi([]));
        $sut = new FileReader($readerMock);
        $sut->readFile(new File($dummyJsonFile));
    }
}

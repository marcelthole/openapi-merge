<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Writer;

use Generator;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Writer\DefinitionWriter;
use Mthole\OpenApiMerge\Writer\Exception\InvalidFileTypeException;
use openapiphp\openapi\spec\OpenApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefinitionWriter::class)]
#[UsesClass('\Mthole\OpenApiMerge\FileHandling\File')]
#[UsesClass('\Mthole\OpenApiMerge\FileHandling\SpecificationFile')]
#[UsesClass('\Mthole\OpenApiMerge\Writer\Exception\InvalidFileTypeException')]
class DefinitionWriterTest extends TestCase
{
    #[DataProvider('validSpecificationFiles')]
    public function testWrite(SpecificationFile $specificationFile): void
    {
        $sut    = new DefinitionWriter();
        $result = $sut->write($specificationFile);
        self::assertNotEmpty($result);
    }

    /** @return Generator<SpecificationFile[]> */
    public static function validSpecificationFiles(): Generator
    {
        $specObject = new OpenApi([]);

        yield [
            new SpecificationFile(
                new File('dummy.yml'),
                $specObject,
            ),
        ];

        yield [
            new SpecificationFile(
                new File('dummy.yaml'),
                $specObject,
            ),
        ];

        yield [
            new SpecificationFile(
                new File('dummy.json'),
                $specObject,
            ),
        ];
    }

    public function testWriteUnspportedExtension(): void
    {
        $specificationFile = new SpecificationFile(
            new File('dummy.neon'),
            new OpenApi([]),
        );

        $sut = new DefinitionWriter();

        self::expectException(InvalidFileTypeException::class);
        $sut->write($specificationFile);
    }

    public function testWriteJson(): void
    {
        $specificationFile = new SpecificationFile(
            new File('dummy.json'),
            new OpenApi(['openapi' => '3.0.0']),
        );

        $sut = new DefinitionWriter();
        self::assertEquals(
            <<<'JSON'
            {
                "openapi": "3.0.0"
            }
            JSON,
            $sut->writeToJson($specificationFile),
        );
    }

    public function testWriteYaml(): void
    {
        $specificationFile = new SpecificationFile(
            new File('dummy.yml'),
            new OpenApi(['openapi' => '3.0.0']),
        );

        $sut = new DefinitionWriter();
        self::assertEquals(
            <<<'YML'
            openapi: 3.0.0

            YML,
            $sut->writeToYaml($specificationFile),
        );
    }
}

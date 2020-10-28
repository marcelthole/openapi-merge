<?php

declare(strict_types=1);

namespace OpenApiMerge\Tests\Writer;

use cebe\openapi\spec\OpenApi;
use Generator;
use OpenApiMerge\FileHandling\File;
use OpenApiMerge\FileHandling\SpecificationFile;
use OpenApiMerge\Writer\DefinitionWriter;
use OpenApiMerge\Writer\Exception\InvalidFileTypeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenApiMerge\Writer\DefinitionWriter
 */
class DefinitionWriterTest extends TestCase
{
    /**
     * @dataProvider validSpecificationFiles
     */
    public function testWrite(SpecificationFile $specificationFile): void
    {
        $sut    = new DefinitionWriter();
        $result = $sut->write($specificationFile);
        self::assertNotEmpty($result);
    }

    /** @return Generator<SpecificationFile[]> */
    public function validSpecificationFiles(): Generator
    {
        $specObject = new OpenApi([]);

        yield [
            new SpecificationFile(
                new File('dummy.yml'),
                $specObject
            ),
        ];

        yield [
            new SpecificationFile(
                new File('dummy.yaml'),
                $specObject
            ),
        ];

        yield [
            new SpecificationFile(
                new File('dummy.json'),
                $specObject
            ),
        ];
    }

    public function testWriteUnspportedExtension(): void
    {
        $specificationFile = new SpecificationFile(
            new File('dummy.neon'),
            new OpenApi([])
        );

        $sut = new DefinitionWriter();

        self::expectException(InvalidFileTypeException::class);
        $sut->write($specificationFile);
    }

    public function testWriteJson(): void
    {
        $specificationFile = new SpecificationFile(
            new File('dummy.json'),
            new OpenApi(['openapi' => '3.0.0'])
        );

        $sut = new DefinitionWriter();
        self::assertEquals(
            <<<'JSON'
            {
                "openapi": "3.0.0"
            }
            JSON,
            $sut->writeToJson($specificationFile)
        );
    }

    public function testWriteYaml(): void
    {
        $specificationFile = new SpecificationFile(
            new File('dummy.yml'),
            new OpenApi(['openapi' => '3.0.0'])
        );

        $sut = new DefinitionWriter();
        self::assertEquals(
            <<<'YML'
            openapi: 3.0.0
            
            YML,
            $sut->writeToYaml($specificationFile)
        );
    }
}

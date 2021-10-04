<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Merge;

use cebe\openapi\Writer;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\Merge\ReferenceNormalizer;
use Mthole\OpenApiMerge\Reader\FileReader;
use PHPUnit\Framework\TestCase;

/**
 * @uses   \Mthole\OpenApiMerge\Reader\FileReader
 * @uses   \Mthole\OpenApiMerge\FileHandling\File
 * @uses   \Mthole\OpenApiMerge\FileHandling\SpecificationFile
 * @uses   \Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper
 *
 * @covers \Mthole\OpenApiMerge\Merge\ReferenceNormalizer
 * @covers \Mthole\OpenApiMerge\Merge\ReferenceResolverResult
 */
class ReferenceNormalizerTest extends TestCase
{
    public function testReadFileWithResolvedReference(): void
    {
        $file       = new File(__DIR__ . '/Fixtures/openapi-with-reference.json');
        $fileReader = new FileReader();
        $openApi    = $fileReader->readFile($file, false)->getOpenApi();

        $sut = new ReferenceNormalizer();

        $specificationResult = $sut->normalizeInlineReferences(
            $file,
            $openApi
        );

        self::assertStringEqualsFile(
            __DIR__ . '/Fixtures/expected/openapi-normalized.json',
            Writer::writeToJson($specificationResult->getNormalizedDefinition())
        );

        $foundRefFiles = $specificationResult->getFoundReferenceFiles();
        self::assertCount(3, $foundRefFiles);
        self::assertSame(
            __DIR__ . '/Fixtures/responseModel.json',
            $foundRefFiles[0]->getAbsoluteFile()
        );
        self::assertSame(
            __DIR__ . '/Fixtures/referenceModel.json',
            $foundRefFiles[1]->getAbsoluteFile()
        );
        self::assertSame(
            __DIR__ . '/Fixtures/sub/examples/referenceModel.json',
            $foundRefFiles[2]->getAbsoluteFile()
        );
    }
}

<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Merge;

use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\Merge\ReferenceNormalizer;
use Mthole\OpenApiMerge\Merge\ReferenceResolverResult;
use Mthole\OpenApiMerge\Reader\FileReader;
use openapiphp\openapi\Writer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReferenceNormalizer::class)]
#[CoversClass(ReferenceResolverResult::class)]
#[UsesClass('\Mthole\OpenApiMerge\Reader\FileReader')]
#[UsesClass('\Mthole\OpenApiMerge\FileHandling\File')]
#[UsesClass('\Mthole\OpenApiMerge\FileHandling\SpecificationFile')]
#[UsesClass('\Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper')]
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
            $openApi,
        );

        self::assertStringEqualsFile(
            __DIR__ . '/Fixtures/expected/openapi-normalized.json',
            Writer::writeToJson($specificationResult->getNormalizedDefinition()),
        );

        $foundRefFiles = $specificationResult->getFoundReferenceFiles();
        self::assertCount(4, $foundRefFiles);
        self::assertSame(
            __DIR__ . '/Fixtures/responseModel.json',
            $foundRefFiles[0]->getAbsoluteFile(),
        );
        self::assertSame(
            __DIR__ . '/Fixtures/referenceModel.json',
            $foundRefFiles[1]->getAbsoluteFile(),
        );
        self::assertSame(
            __DIR__ . '/Fixtures/sub/examples/referenceModel.json',
            $foundRefFiles[2]->getAbsoluteFile(),
        );
        self::assertSame(
            __DIR__ . '/Fixtures/sub/examples/subType.json',
            $foundRefFiles[3]->getAbsoluteFile(),
        );
    }
}

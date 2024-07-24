<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Merge;

use Mthole\OpenApiMerge\FileHandling\Exception\IOException;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use Mthole\OpenApiMerge\Merge\ReferenceNormalizer;
use Mthole\OpenApiMerge\Merge\ReferenceResolverResult;
use Mthole\OpenApiMerge\Reader\FileReader;
use Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper;
use openapiphp\openapi\Writer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function count;

#[CoversClass(ReferenceNormalizer::class)]
#[CoversClass(ReferenceResolverResult::class)]
#[UsesClass(FileReader::class)]
#[UsesClass(File::class)]
#[UsesClass(SpecificationFile::class)]
#[UsesClass(OpenApiReaderWrapper::class)]
#[UsesClass(IOException::class)]
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

        $foundRefFiles    = $specificationResult->getFoundReferenceFiles();
        $expectedRefFiles = [
            __DIR__ . '/Fixtures/pathOutsideOperation.json',
            __DIR__ . '/Fixtures/requestParam.json',
            __DIR__ . '/Fixtures/requestParamNullable.json',
            __DIR__ . '/Fixtures/responseModel.json',
            __DIR__ . '/Fixtures/referenceModel.json',
            __DIR__ . '/Fixtures/sub/examples/referenceModel.json',
            __DIR__ . '/Fixtures/sub/examples/subType.json',
            __DIR__ . '/Fixtures/requestBody.json',
            __DIR__ . '/Fixtures/requestBodyListItem.json',
            __DIR__ . '/Fixtures/additionalProperties.json',
        ];
        self::assertCount(count($expectedRefFiles), $foundRefFiles);
        foreach ($expectedRefFiles as $expectedRefFile) {
            $file = array_shift($foundRefFiles);
            self::assertNotNull($file);
            self::assertSame($expectedRefFile, $file->getAbsoluteFile());
        }
    }
}

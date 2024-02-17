<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests;

use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\Merge\ComponentsMerger;
use Mthole\OpenApiMerge\Merge\PathMerger;
use Mthole\OpenApiMerge\Merge\ReferenceNormalizer;
use Mthole\OpenApiMerge\Merge\ReferenceResolverResult;
use Mthole\OpenApiMerge\OpenApiMerge;
use Mthole\OpenApiMerge\Reader\FileReader;
use openapiphp\openapi\spec\Components;
use openapiphp\openapi\spec\OpenApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function assert;

#[CoversClass(OpenApiMerge::class)]
#[UsesClass('\Mthole\OpenApiMerge\FileHandling\File')]
#[UsesClass('\Mthole\OpenApiMerge\FileHandling\SpecificationFile')]
#[UsesClass('\Mthole\OpenApiMerge\Reader\FileReader')]
#[UsesClass('\Mthole\OpenApiMerge\Merge\PathMerger')]
#[UsesClass('\Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper')]
#[UsesClass('\Mthole\OpenApiMerge\Merge\ReferenceResolverResult')]
#[UsesClass('\Mthole\OpenApiMerge\Merge\ComponentsMerger')]
#[UsesClass('\Mthole\OpenApiMerge\Util\Json')]
class OpenApiMergeTest extends TestCase
{
    public function testMergePaths(): void
    {
        $sut = new OpenApiMerge(
            new FileReader(),
            [
                new PathMerger(),
                new ComponentsMerger(),
            ],
            new ReferenceNormalizer(),
        );

        $result = $sut->mergeFiles(
            new File(__DIR__ . '/Fixtures/base.yml'),
            [
                new File(__DIR__ . '/Fixtures/empty.yml'),
                new File(__DIR__ . '/Fixtures/routes.yml'),
                new File(__DIR__ . '/Fixtures/errors.yml'),
            ],
        )->getOpenApi();
        assert($result instanceof OpenApi);

        self::assertCount(1, $result->paths->getPaths());
        self::assertNotNull($result->components);
        self::assertIsArray($result->components->schemas);
    }

    public function testMergeFileWithoutComponents(): void
    {
        $sut = new OpenApiMerge(
            new FileReader(),
            [
                new PathMerger(),
                new ComponentsMerger(),
            ],
            new ReferenceNormalizer(),
        );

        $result = $sut->mergeFiles(
            new File(__DIR__ . '/Fixtures/base-without-components.yml'),
            [],
        )->getOpenApi();
        assert($result instanceof OpenApi);

        self::assertNull($result->components);
    }

    public function testReferenceNormalizer(): void
    {
        $referenceNormalizer = $this->createMock(ReferenceNormalizer::class);
        $referenceNormalizer->expects(
            self::exactly(2),
        )->method('normalizeInlineReferences')->willReturnCallback(static function (
            File $openApiFile,
            OpenApi $openApiDefinition,
        ) {
            $foundReferences = [];
            if ($openApiFile->getAbsoluteFile() === __DIR__ . '/Fixtures/errors.yml') {
                $foundReferences[] = new File(__DIR__ . '/Fixtures/routes.yml');
            }

            return new ReferenceResolverResult(
                $openApiDefinition,
                $foundReferences,
            );
        });

        $sut = new OpenApiMerge(
            new FileReader(),
            [
                new PathMerger(),
                new ComponentsMerger(),
            ],
            $referenceNormalizer,
        );

        $mergedResult = $sut->mergeFiles(
            new File(__DIR__ . '/Fixtures/base.yml'),
            [
                new File(__DIR__ . '/Fixtures/errors.yml'),
            ],
            false,
        );

        $mergedDefinition = $mergedResult->getOpenApi();
        if ($mergedDefinition->components === null) {
            $mergedDefinition->components = new Components([]);
        }

        self::assertCount(1, $mergedDefinition->paths);
        self::assertSame(
            ['ProblemResponse', 'pingResponse'],
            array_keys($mergedDefinition->components->schemas),
        );
    }

    public function testReferenceNormalizerWillNotBeExecuted(): void
    {
        $referenceNormalizer = $this->createMock(ReferenceNormalizer::class);
        $referenceNormalizer->expects(self::never())->method('normalizeInlineReferences');

        $sut = new OpenApiMerge(
            new FileReader(),
            [],
            $referenceNormalizer,
        );

        $sut->mergeFiles(
            new File(__DIR__ . '/Fixtures/base.yml'),
            [
                new File(__DIR__ . '/Fixtures/errors.yml'),
            ],
        );
    }
}

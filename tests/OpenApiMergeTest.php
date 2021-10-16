<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests;

use cebe\openapi\spec\Components;
use cebe\openapi\spec\OpenApi;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\Merge\PathMerger;
use Mthole\OpenApiMerge\Merge\ReferenceNormalizer;
use Mthole\OpenApiMerge\Merge\ReferenceResolverResult;
use Mthole\OpenApiMerge\OpenApiMerge;
use Mthole\OpenApiMerge\Reader\FileReader;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function assert;

/**
 * @uses \Mthole\OpenApiMerge\FileHandling\File
 * @uses \Mthole\OpenApiMerge\FileHandling\SpecificationFile
 * @uses \Mthole\OpenApiMerge\Reader\FileReader
 * @uses \Mthole\OpenApiMerge\Merge\PathMerger
 * @uses \Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper
 * @uses \Mthole\OpenApiMerge\Merge\ReferenceResolverResult
 *
 * @covers \Mthole\OpenApiMerge\OpenApiMerge
 */
class OpenApiMergeTest extends TestCase
{
    public function testMergePaths(): void
    {
        $sut = new OpenApiMerge(
            new FileReader(),
            new PathMerger(),
            new ReferenceNormalizer()
        );

        $result = $sut->mergeFiles(
            new File(__DIR__ . '/Fixtures/base.yml'),
            [
                new File(__DIR__ . '/Fixtures/empty.yml'),
                new File(__DIR__ . '/Fixtures/routes.yml'),
                new File(__DIR__ . '/Fixtures/errors.yml'),
            ]
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
            new PathMerger(),
            new ReferenceNormalizer()
        );

        $result = $sut->mergeFiles(
            new File(__DIR__ . '/Fixtures/base-without-components.yml'),
            []
        )->getOpenApi();
        assert($result instanceof OpenApi);

        self::assertNull($result->components);
    }

    public function testReferenceNormalizer(): void
    {
        $referenceNormalizer = $this->createMock(ReferenceNormalizer::class);
        $referenceNormalizer->expects(
            self::exactly(2)
        )->method('normalizeInlineReferences')->willReturnCallback(static function (
            File $openApiFile,
            OpenApi $openApiDefinition
        ) {
            $foundReferences = [];
            if ($openApiFile->getAbsoluteFile() === __DIR__ . '/Fixtures/errors.yml') {
                $foundReferences[] = new File(__DIR__ . '/Fixtures/routes.yml');
            }

            return new ReferenceResolverResult(
                $openApiDefinition,
                $foundReferences
            );
        });

        $sut = new OpenApiMerge(
            new FileReader(),
            new PathMerger(),
            $referenceNormalizer
        );

        $mergedResult = $sut->mergeFiles(
            new File(__DIR__ . '/Fixtures/base.yml'),
            [
                new File(__DIR__ . '/Fixtures/errors.yml'),
            ],
            false
        );

        $mergedDefinition = $mergedResult->getOpenApi();
        if ($mergedDefinition->components === null) {
            $mergedDefinition->components = new Components([]);
        }

        self::assertCount(1, $mergedDefinition->paths);
        self::assertSame(
            ['ProblemResponse', 'pingResponse'],
            array_keys($mergedDefinition->components->schemas)
        );
    }

    public function testReferenceNormalizerWillNotBeExecuted(): void
    {
        $referenceNormalizer = $this->createMock(ReferenceNormalizer::class);
        $referenceNormalizer->expects(self::never())->method('normalizeInlineReferences');

        $sut = new OpenApiMerge(
            new FileReader(),
            new PathMerger(),
            $referenceNormalizer
        );

        $sut->mergeFiles(
            new File(__DIR__ . '/Fixtures/base.yml'),
            [
                new File(__DIR__ . '/Fixtures/errors.yml'),
            ]
        );
    }
}

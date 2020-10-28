<?php

declare(strict_types=1);

namespace OpenApiMerge\Tests;

use cebe\openapi\spec\OpenApi;
use OpenApiMerge\FileHandling\File;
use OpenApiMerge\OpenApiMerge;
use OpenApiMerge\Reader\FileReader;
use PHPUnit\Framework\TestCase;

use function assert;

/**
 * @covers \OpenApiMerge\OpenApiMerge
 */
class OpenApiMergeTest extends TestCase
{
    public function testMergePaths(): void
    {
        $sut = new OpenApiMerge(
            new FileReader()
        );

        $result = $sut->mergeFiles(
            new File(__DIR__ . '/Fixtures/base.yml'),
            new File(__DIR__ . '/Fixtures/routes.yml'),
            new File(__DIR__ . '/Fixtures/errors.yml')
        )->getOpenApiSpecificationObject();
        assert($result instanceof OpenApi);

        self::assertCount(1, $result->paths->getPaths());
    }
}

<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests;

use cebe\openapi\spec\OpenApi;
use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\OpenApiMerge;
use Mthole\OpenApiMerge\Reader\FileReader;
use PHPUnit\Framework\TestCase;

use function assert;

/**
 * @covers \Mthole\OpenApiMerge\OpenApiMerge
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
        )->getOpenApi();
        assert($result instanceof OpenApi);

        self::assertCount(1, $result->paths->getPaths());
    }
}

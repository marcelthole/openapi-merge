<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Reader;

use Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper;
use openapiphp\openapi\spec\OpenApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OpenApiReaderWrapper::class)]
class OpenApiReaderWrapperTest extends TestCase
{
    public function testCall(): void
    {
        $sut      = new OpenApiReaderWrapper();
        $jsonSpec = $sut->readFromJsonFile(
            __DIR__ . '/Fixtures/valid-openapi.json',
            OpenApi::class,
            true,
        );
        self::assertSame('3.0.0', $jsonSpec->openapi);
        $yamlSpec = $sut->readFromYamlFile(
            __DIR__ . '/Fixtures/valid-openapi.yml',
            OpenApi::class,
            true,
        );
        self::assertSame('3.0.0', $yamlSpec->openapi);
    }
}

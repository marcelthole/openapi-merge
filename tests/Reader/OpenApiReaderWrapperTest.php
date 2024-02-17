<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Reader;

use Mthole\OpenApiMerge\Reader\OpenApiReaderWrapper;
use openapiphp\openapi\spec\OpenApi;
use openapiphp\openapi\SpecObjectInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OpenApiReaderWrapper::class)]
class OpenApiReaderWrapperTest extends TestCase
{
    public function testCall(): void
    {
        $sut = new OpenApiReaderWrapper();
        self::assertInstanceOf(
            SpecObjectInterface::class,
            $sut->readFromJsonFile(
                __DIR__ . '/Fixtures/valid-openapi.json',
                OpenApi::class,
                true,
            ),
        );
        self::assertInstanceOf(
            SpecObjectInterface::class,
            $sut->readFromYamlFile(
                __DIR__ . '/Fixtures/valid-openapi.yml',
                OpenApi::class,
                true,
            ),
        );
    }
}

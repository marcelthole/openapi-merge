<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\FileHandling;

use Mthole\OpenApiMerge\FileHandling\File;
use Mthole\OpenApiMerge\FileHandling\SpecificationFile;
use openapiphp\openapi\spec\OpenApi;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SpecificationFile::class)]
#[UsesClass(File::class)]
class SpecificationFileTest extends TestCase
{
    public function testGetter(): void
    {
        $stubSpecObject = new OpenApi([]);
        $fileStub       = new File('example.file');
        $sut            = new SpecificationFile($fileStub, $stubSpecObject);

        self::assertSame($fileStub, $sut->getFile());
        self::assertSame($stubSpecObject, $sut->getOpenApi());
    }
}

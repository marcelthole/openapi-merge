<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Console\IO;

use Mthole\OpenApiMerge\Console\IO\DummyWriter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mthole\OpenApiMerge\Console\IO\DummyWriter
 */
class DummyWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $sut = new DummyWriter();
        $sut->write('Dummy');

        self::assertSame(['Dummy'], $sut->getMessages());
    }
}

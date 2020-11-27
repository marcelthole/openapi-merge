<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Console\IO;

use Mthole\OpenApiMerge\Console\IO\PrintWriter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mthole\OpenApiMerge\Console\IO\PrintWriter
 */
class PrintWriterTest extends TestCase
{
    public function testWrite(): void
    {
        $sut = new PrintWriter();
        $this->expectOutputString('Dummy');
        $sut->write('Dummy');
    }
}

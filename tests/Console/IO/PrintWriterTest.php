<?php

declare(strict_types=1);

namespace OpenApiMerge\Tests\Console\IO;

use OpenApiMerge\Console\IO\PrintWriter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenApiMerge\Console\IO\PrintWriter
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

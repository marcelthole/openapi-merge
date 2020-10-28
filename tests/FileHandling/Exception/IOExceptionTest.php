<?php

declare(strict_types=1);

namespace OpenApiMerge\Tests\FileHandling\Exception;

use OpenApiMerge\FileHandling\Exception\IOException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenApiMerge\FileHandling\Exception\IOException
 */
class IOExceptionTest extends TestCase
{
    public function testCreateException(): void
    {
        $exception = IOException::createWithNonExistingFile('dummyfile');
        self::assertSame('dummyfile', $exception->getFilename());
        self::assertSame('Given file "dummyfile" was not found', $exception->getMessage());
    }
}

<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Reader\Exception;

use Mthole\OpenApiMerge\Reader\Exception\InvalidFileTypeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mthole\OpenApiMerge\Reader\Exception\InvalidFileTypeException
 */
class InvalidFileTypeExceptionTest extends TestCase
{
    public function testCreateException(): void
    {
        $exception = InvalidFileTypeException::createFromExtension('exe');
        self::assertSame('exe', $exception->getFileExtension());
        self::assertSame('Given file has an unsupported file extension "exe"', $exception->getMessage());
    }
}

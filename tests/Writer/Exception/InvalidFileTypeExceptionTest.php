<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Writer\Exception;

use Mthole\OpenApiMerge\Writer\Exception\InvalidFileTypeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Mthole\OpenApiMerge\Writer\Exception\InvalidFileTypeException
 */
class InvalidFileTypeExceptionTest extends TestCase
{
    public function testCreateException(): void
    {
        $exception = InvalidFileTypeException::createFromExtension('exe');
        self::assertSame('exe', $exception->getFileExtension());
        self::assertSame('The filetype "exe" is not supported for dumping', $exception->getMessage());
    }
}

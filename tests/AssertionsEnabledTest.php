<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests;

use PHPUnit\Framework\TestCase;
use Throwable;

use function assert;

/**
 * @coversNothing
 */
class AssertionsEnabledTest extends TestCase
{
    public function testAssertionsWillThrowAnException(): void
    {
        self::expectException(Throwable::class);
        assert(false); // @phpstan-ignore-line
        $this->fail('php `assert` didn\'t throw an exception. Set the `zend.assertions` to `1` in the php.ini');
    }
}

<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Util;

use Mthole\OpenApiMerge\Util\Json;
use PHPUnit\Framework\TestCase;

/** @covers \Mthole\OpenApiMerge\Util\Json */
class JsonTest extends TestCase
{
    /**
     * @param array<mixed> $expected
     *
     * @dataProvider toArrayDataprovider
     */
    public function testToArray(mixed $data, array $expected): void
    {
        self::assertSame($expected, Json::toArray($data));
    }

    /** @return iterable<string, list<mixed|array<mixed>>> */
    public static function toArrayDataprovider(): iterable
    {
        yield 'string' => [
            'dummy',
            ['dummy'],
        ];

        yield 'int' => [
            1,
            [1],
        ];

        yield 'false' => [
            false,
            [false],
        ];

        yield 'array' => [
            ['foo' => 'bar'],
            ['foo' => 'bar'],
        ];

        yield 'object' => [
            (object) ['foo' => 'bar'],
            ['foo' => 'bar'],
        ];
    }
}

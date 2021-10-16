<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\FileHandling;

use Mthole\OpenApiMerge\FileHandling\RegexFinder;
use PHPUnit\Framework\TestCase;

use function array_keys;

/**
 * @covers \Mthole\OpenApiMerge\FileHandling\RegexFinder
 */
class RegexFinderTest extends TestCase
{
    /**
     * @dataProvider findStringDataProvider
     */
    public function testFind(string $search, int $expectedFilesCount): void
    {
        $sut   = new RegexFinder();
        $files = $sut->find(__DIR__ . '/Fixtures', $search);
        self::assertCount($expectedFilesCount, $files);
        foreach (array_keys($files) as $key) {
            self::assertIsNumeric($key);
        }
    }

    /**
     * @return array<string, array<string|int>>
     */
    public function findStringDataProvider(): iterable
    {
        yield 'all a.txt' => ['.*a.txt', 2];
        yield 'all a.txt in B' => ['B/a.txt', 1];
        yield 'all *.txt' => ['.*.txt', 4];
        yield 'all in A' => ['^./A/.*', 2];
        yield 'only files' => ['.*', 5];
        yield 'find regex with ~ delimiter file' => ['~home.html$', 1];
    }
}

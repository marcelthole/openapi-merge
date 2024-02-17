<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Merge;

use Mthole\OpenApiMerge\Merge\PathMerger;
use openapiphp\openapi\spec\OpenApi;
use openapiphp\openapi\spec\PathItem;
use openapiphp\openapi\spec\Paths;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function array_keys;

#[CoversClass(PathMerger::class)]
#[UsesClass('\Mthole\OpenApiMerge\Util\Json')]
class PathMergerTest extends TestCase
{
    public function testMergeDidNotChangeOriginals(): void
    {
        $existingPath = new Paths(['/route1' => new PathItem([])]);
        $newPaths     = new Paths([
            '/route2' => new PathItem([]),
        ]);

        $existingSpec = new OpenApi(['paths' => $existingPath]);
        $newSpec      = new OpenApi(['paths' => $newPaths]);

        $sut     = new PathMerger();
        $newSpec = $sut->merge($existingSpec, $newSpec);
        self::assertCount(1, $existingPath);
        self::assertCount(1, $newPaths);
        self::assertCount(2, $newSpec->paths);
    }

    /**
     * @param Paths<PathItem>              $existingPaths
     * @param Paths<PathItem>              $newPaths
     * @param array<string>                $expectedRoutes
     * @param array<string, array<string>> $expectedMethods
     */
    #[DataProvider('pathCombinationDataProvider')]
    public function testMergePaths(
        Paths $existingPaths,
        Paths $newPaths,
        array $expectedRoutes,
        array $expectedMethods,
    ): void {
        $sut         = new PathMerger();
        $mergedPaths = $sut->merge(
            new OpenApi(['paths' => $existingPaths]),
            new OpenApi(['paths' => $newPaths]),
        );

        self::assertSame($expectedRoutes, array_keys($mergedPaths->paths->getPaths()));

        foreach ($expectedMethods as $routeName => $expectedRouteMethods) {
            $pathItem = $mergedPaths->paths->getPath($routeName);
            self::assertNotNull($pathItem);
            self::assertSame(
                $expectedRouteMethods,
                array_keys($pathItem->getOperations()),
            );
        }
    }

    /** @return iterable<string, list<mixed>> */
    public static function pathCombinationDataProvider(): iterable
    {
        yield 'simple routes' => [
            new Paths(['/route1' => new PathItem([])]),
            new Paths(['/route2' => new PathItem([])]),
            ['/route1','/route2'],
            ['/route1' => [],'/route2' => []],
        ];

        yield 'same routes with get and post' => [
            new Paths(['/route1' => new PathItem(['post' => ['operationId' => 'post-route1']])]),
            new Paths(['/route1' => new PathItem(['get' => ['operationId' => 'get-route1']])]),
            ['/route1'],
            ['/route1' => ['get','post']],
        ];

        yield 'non existing routes in original' => [
            new Paths([
                '/route1' => new PathItem(['get' => ['operationId' => 'get-route1']]),
                '/route2' => new PathItem(['trace' => ['operationId' => 'trace-route2']]),
            ]),
            new Paths([
                '/route3' => new PathItem(['post' => ['operationId' => 'post-route3']]),
                '/route1' => new PathItem(['get' => ['operationId' => 'get-route1-no-merge']]),
                '/route2' => new PathItem(['get' => ['operationId' => 'get-route2']]),
            ]),
            ['/route1','/route2','/route3'],
            ['/route1' => ['get'], '/route2' => ['get','trace'], '/route3' => ['post']],
        ];

        yield 'multiple methods in one path' => [
            new Paths([
                '/route1' => new PathItem(['get' => ['operationId' => 'get-route1']]),
            ]),
            new Paths([
                '/route1' => new PathItem([
                    'put' => ['operationId' => 'put-route1'],
                    'post' => ['operationId' => 'put-route1'],
                ]),
            ]),
            ['/route1'],
            ['/route1' => ['get','post','put']],
        ];

        yield 'explicit null method' => [
            new Paths([
                '/route1' => new PathItem(['get' => ['operationId' => 'get-route1']]),
            ]),
            new Paths([
                '/route1' => new PathItem([
                    'get' => null,
                    'post' => null,
                    'put' => null,
                    'patch' => ['operationId' => 'patch-route1'],
                ]),
            ]),
            ['/route1'],
            ['/route1' => ['get','patch']],
        ];
    }
}

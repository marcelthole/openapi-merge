<?php

declare(strict_types=1);

namespace Merge;

use cebe\openapi\spec\Components;
use cebe\openapi\spec\OpenApi;
use Mthole\OpenApiMerge\Merge\ComponentsMerger;
use PHPUnit\Framework\TestCase;

/**
 * @uses \Mthole\OpenApiMerge\Util\Json
 *
 * @covers \Mthole\OpenApiMerge\Merge\ComponentsMerger
 */
class ComponentsMergerTest extends TestCase
{
    /** @dataProvider mergeDataProvider */
    public function testMerge(
        Components|null $existingComponents,
        Components|null $newComponents,
        Components|null $expectedComponents,
    ): void {
        $sut          = new ComponentsMerger();
        $existingSpec = new OpenApi(['components' => $existingComponents]);
        $newSpec      = new OpenApi(['components' => $newComponents]);
        $expectedSpec = new OpenApi(['components' => $expectedComponents]);

        $stateBefore = $existingSpec->getSerializableData();
        self::assertEquals($expectedSpec, $sut->merge($existingSpec, $newSpec));
        self::assertEquals($stateBefore, $existingSpec->getSerializableData());
    }

    /** @return iterable<string, list<Components|null>> */
    public function mergeDataProvider(): iterable
    {
        yield 'empty' => [
            new Components([]),
            new Components([]),
            new Components([]),
        ];

        yield 'null first' => [
            null,
            new Components([]),
            new Components([]),
        ];

        yield 'null second' => [
            new Components([]),
            null,
            new Components([]),
        ];

        yield 'null all' => [
            null,
            null,
            new Components([]),
        ];

        yield 'schemas first' => [
            new Components([
                'schemas' => [
                    'ProblemResponse' => [],
                ],
            ]),
            null,
            new Components([
                'schemas' => [
                    'ProblemResponse' => [],
                ],
            ]),
        ];

        yield 'schemas second' => [
            null,
            new Components([
                'schemas' => [
                    'ProblemResponse' => [],
                ],
            ]),
            new Components([
                'schemas' => [
                    'ProblemResponse' => [],
                ],
            ]),
        ];

        yield 'schemas both' => [
            new Components([
                'schemas' => [
                    'ProblemResponse' => [],
                ],
            ]),
            new Components([
                'schemas' => [
                    'ProblemResponse2' => [],
                ],
            ]),
            new Components([
                'schemas' => [
                    'ProblemResponse' => [],
                    'ProblemResponse2' => [],
                ],
            ]),
        ];

        yield 'security first' => [
            new Components([
                'securitySchemes' => [
                    'basic' => [],
                ],
            ]),
            null,
            new Components([
                'securitySchemes' => [
                    'basic' => [],
                ],
            ]),
        ];

        yield 'security second' => [
            null,
            new Components([
                'securitySchemes' => [
                    'basic' => [],
                ],
            ]),
            new Components([
                'securitySchemes' => [
                    'basic' => [],
                ],
            ]),
        ];

        yield 'security both' => [
            new Components([
                'securitySchemes' => [
                    'basic' => [],
                ],
            ]),
            new Components([
                'securitySchemes' => [
                    'oauth' => [],
                ],
            ]),
            new Components([
                'securitySchemes' => [
                    'basic' => [],
                    'oauth' => [],
                ],
            ]),
        ];

        yield 'request bodies first' => [
            new Components([
                'requestBodies' => [
                    'RequestBody' => [],
                ],
            ]),
            null,
            new Components([
                'requestBodies' => [
                    'RequestBody' => [],
                ],
            ]),
        ];

        yield 'request bodies second' => [
            null,
            new Components([
                'requestBodies' => [
                    'RequestBody' => [],
                ],
            ]),
            new Components([
                'requestBodies' => [
                    'RequestBody' => [],
                ],
            ]),
        ];

        yield 'request bodies both' => [
            new Components([
                'requestBodies' => [
                    'RequestBody' => [],
                ],
            ]),
            new Components([
                'requestBodies' => [
                    'AnotherRequestBody' => [],
                ],
            ]),
            new Components([
                'requestBodies' => [
                    'RequestBody' => [],
                    'AnotherRequestBody' => [],
                ],
            ]),
        ];

        yield 'responses first' => [
            new Components([
                'responses' => [
                    'ProblemResponse' => [],
                ],
            ]),
            null,
            new Components([
                'responses' => [
                    'ProblemResponse' => [],
                ],
            ]),
        ];

        yield 'responses second' => [
            null,
            new Components([
                'responses' => [
                    'ProblemResponse' => [],
                ],
            ]),
            new Components([
                'responses' => [
                    'ProblemResponse' => [],
                ],
            ]),
        ];

        yield 'responses both' => [
            new Components([
                'responses' => [
                    'ProblemResponse' => [],
                ],
            ]),
            new Components([
                'responses' => [
                    'AnotherProblemResponse' => [],
                ],
            ]),
            new Components([
                'responses' => [
                    'ProblemResponse' => [],
                    'AnotherProblemResponse' => [],
                ],
            ]),
        ];
    }
}

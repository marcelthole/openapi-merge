<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Acceptance;

use PHPUnit\Framework\TestCase;

use function implode;
use function shell_exec;
use function sprintf;
use function version_compare;

use const PHP_VERSION;

/**
 * @coversNothing
 */
class ApplicationAcceptanceTest extends TestCase
{
    public function testApplicationRuns(): void
    {
        $output = shell_exec(sprintf(
            'php %s %s',
            __DIR__ . '/../../bin/openapi-merge',
            implode(' ', [
                __DIR__ . '/Fixtures/base.yml',
                __DIR__ . '/Fixtures/routes.yml',
                __DIR__ . '/Fixtures/routes_merge.yml',
                __DIR__ . '/Fixtures/errors.yml',
            ])
        ));

        self::assertNotNull($output);

        if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
            self::assertStringEqualsFile(__DIR__ . '/Fixtures/expected_php81.yml', $output);
        } else {
            self::assertStringEqualsFile(__DIR__ . '/Fixtures/expected_php80.yml', $output);
        }
    }
}

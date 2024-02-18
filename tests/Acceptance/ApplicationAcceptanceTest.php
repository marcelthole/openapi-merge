<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Acceptance;

use Composer\InstalledVersions;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function implode;
use function shell_exec;
use function sprintf;
use function version_compare;

#[CoversNothing]
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
            ]),
        ));

        self::assertNotNull($output);
        self::assertNotFalse($output);

        $yamlVersion = InstalledVersions::getVersion('symfony/yaml') ?? '1.0';
        if (version_compare($yamlVersion, '6.1.0', '<')) {
            self::assertStringEqualsFile(__DIR__ . '/Fixtures/expected_yaml610.yml', $output);
        } else {
            self::assertStringEqualsFile(__DIR__ . '/Fixtures/expected.yml', $output);
        }
    }
}

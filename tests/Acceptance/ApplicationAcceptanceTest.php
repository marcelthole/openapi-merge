<?php

declare(strict_types=1);

namespace Mthole\OpenApiMerge\Tests\Acceptance;

use PHPUnit\Framework\TestCase;

use function shell_exec;
use function sprintf;

/**
 * @coversNothing
 */
class ApplicationAcceptanceTest extends TestCase
{
    public function testApplicationRuns(): void
    {
        $output = shell_exec(sprintf(
            'php %s %s %s %s',
            __DIR__ . '/../../bin/openapi-merge',
            __DIR__ . '/Fixtures/base.yml',
            __DIR__ . '/Fixtures/routes.yml',
            __DIR__ . '/Fixtures/errors.yml'
        ));

        self::assertSame(
            <<<'EXPECTED_YAML'
            openapi: 3.0.2
            info:
              title: 'Example OpenAPI Definition'
              description: 'This is the example Description'
              contact:
                name: 'Base Author'
                url: base.example.org
                email: base-file@example.org
              license:
                name: MIT
                url: 'https://tldrlegal.com/license/mit-license'
              version: '1.0'
            servers:
              -
                url: 'https://api.base.example.org'
                description: 'Main Base URL'
            paths:
              /ping:
                get:
                  tags:
                    - 'Base Route'
                  summary: 'Your GET endpoint'
                  description: 'Description of Ping'
                  operationId: get-ping
                  parameters:
                    -
                      name: responseWith
                      in: query
                      description: 'response with this message'
                      schema:
                        maxLength: 20
                        minLength: 0
                        type: string
                  responses:
                    '200':
                      description: OK
                      content:
                        application/json:
                          schema:
                            required:
                              - response
                            type: object
                            properties:
                              response:
                                type: string
                    '400':
                      description: 'Bad Request'
                      content:
                        application/problem+json:
                          schema:
                            title: ProblemResponse
                            required:
                              - type
                              - title
                            type: object
                            properties:
                              type:
                                type: string
                                description: 'type of the problem'
                                example: ValidationError
                              title:
                                type: string
                                example: 'Your request parameters didn''t validate.'
                            description: 'Default Problem Response'
            components:
              schemas: []
            security: []
            tags:
              -
                name: Base

            EXPECTED_YAML,
            $output
        );
    }
}

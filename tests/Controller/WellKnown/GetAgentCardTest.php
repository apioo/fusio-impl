<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\System\Api\WellKnown;

use Fusio\Impl\Base;
use Fusio\Impl\Tests\DbTestCase;

/**
 * GetAgentCardTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetAgentCardTest extends DbTestCase
{
    public function testGet(): void
    {
        $response = $this->sendRequest('/.well-known/agent-card.json', 'GET', [
            'User-Agent' => 'Fusio TestCase',
        ]);

        $actual = (string) $response->getBody();
        $data = json_decode($actual, true);

        $this->assertEquals(200, $response->getStatusCode(), $actual);

        // the version depends on the installed Fusio version
        $this->assertSame(Base::getVersion() ?? '0.0.0', $data['version'] ?? null, $actual);
        unset($data['version']);

        $expect = <<<'JSON'
{
    "name": "Fusio-Architect",
    "description": "Builds complete API operations by coordinating schemas, database tables, and business logic.",
    "supportedInterfaces": [
        {
            "url": "http://127.0.0.1/a2a/v1",
            "protocolBinding": "JSONRPC",
            "protocolVersion": "1.0"
        }
    ],
    "capabilities": {
        "streaming": false,
        "pushNotifications": false,
        "extendedAgentCard": false
    },
    "defaultInputModes": ["text/plain", "application/json"],
    "defaultOutputModes": ["application/json"],
    "skills": [
        {
            "id": "Fusio-Architect",
            "name": "Fusio-Architect",
            "description": "Builds complete API operations by coordinating schemas, database tables, and business logic.",
            "tags": ["fusio", "architect"],
            "inputModes": ["text/plain", "application/json"],
            "outputModes": ["application/json"]
        }
    ],
    "securitySchemes": {
        "oauth2": {
            "oauth2SecurityScheme": {
                "description": "Fusio OAuth2 authorization, the token requires the \"backend.agent\" scope",
                "flows": {
                    "authorizationCode": {
                        "authorizationUrl": "http://127.0.0.1/authorization/authorize",
                        "tokenUrl": "http://127.0.0.1/authorization/token",
                        "scopes": {
                            "backend": "Access to the backend scope",
                            "backend.agent": "Access to the backend.agent scope"
                        }
                    }
                }
            }
        },
        "oauth2ClientCredentials": {
            "oauth2SecurityScheme": {
                "description": "Fusio OAuth2 client credentials, the app requires the \"backend.agent\" scope",
                "flows": {
                    "clientCredentials": {
                        "tokenUrl": "http://127.0.0.1/authorization/token",
                        "scopes": {
                            "backend": "Access to the backend scope",
                            "backend.agent": "Access to the backend.agent scope"
                        }
                    }
                }
            }
        }
    },
    "securityRequirements": [
        {"schemes": {"oauth2": {"list": ["backend.agent"]}}},
        {"schemes": {"oauth2ClientCredentials": {"list": ["backend.agent"]}}}
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, (string) json_encode($data), $actual);
    }

    public function testPost(): void
    {
        $response = $this->sendRequest('/.well-known/api-catalog', 'POST', [
            'User-Agent' => 'Fusio TestCase',
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut(): void
    {
        $response = $this->sendRequest('/.well-known/api-catalog', 'PUT', [
            'User-Agent' => 'Fusio TestCase',
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete(): void
    {
        $response = $this->sendRequest('/.well-known/api-catalog', 'DELETE', [
            'User-Agent' => 'Fusio TestCase',
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

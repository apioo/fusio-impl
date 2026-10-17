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
        $expect = <<<JSON
{
    "name": "Fusio",
    "description": "Self-Hosted API Management for Builders.",
    "url": "http:\/\/127.0.0.1\/a2a\/v1",
    "skills": [
        {
            "id": "agent-test",
            "name": "agent-test",
            "description": "An agent test"
        },
        {
            "id": "Fusio-Seed",
            "name": "Fusio-Seed",
            "description": "Populates tables with context-aware data. Generates realistic test records or accurate factual data for production."
        },
        {
            "id": "Fusio-Database",
            "name": "Fusio-Database",
            "description": "Designs database table structures including columns, types, and constraints."
        },
        {
            "id": "Fusio-Schema",
            "name": "Fusio-Schema",
            "description": "Designs JSON schemas to define and validate request\/response data structures."
        },
        {
            "id": "Fusio-Action",
            "name": "Fusio-Action",
            "description": "Develops custom business logic and backend code for your API operations."
        },
        {
            "id": "Fusio-Architect",
            "name": "Fusio-Architect",
            "description": "Builds complete API operations by coordinating schemas, database tables, and business logic."
        },
        {
            "id": "Fusio-General",
            "name": "Fusio-General",
            "description": "Provides real-time instance insights and debugging. Explores your setup to analyze operations, tables, and logs."
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
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

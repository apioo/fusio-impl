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

namespace Fusio\Impl\Tests\Backend\Api\Action;

use Fusio\Engine\Inflection\ClassName;
use Fusio\Impl\System\Action\Meta\GetDebug;
use Fusio\Impl\Tests\Authorization;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Normalizer;

/**
 * ExecuteTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ExecuteTest extends DbTestCase
{
    public function testGet(): void
    {
        $response = $this->sendRequest('/backend/action/3/execute', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
        ]);

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPost(): void
    {
        $response = $this->sendRequest('/backend/action/' . ClassName::serialize(GetDebug::class) . '/execute', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
        ], json_encode([
            'method'       => 'GET',
            'uriFragments' => 'news_id=10',
            'parameters'   => 'count=10',
            'headers'      => 'Content-Type=application/json',
            'body'         => new \stdClass(),
        ]));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "kind": "[kind]",
    "statusCode": 200,
    "headers": {},
    "body": {
        "class": "Fusio\\Engine\\Request",
        "arguments": {
            "count": "10",
            "news_id": "10"
        },
        "payload": {},
        "context": "Fusio\\Engine\\Request\\HttpRequestContext"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut(): void
    {
        $response = $this->sendRequest('/backend/action/3/execute', 'PUT', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete(): void
    {
        $response = $this->sendRequest('/backend/action/3/execute', 'DELETE', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => Authorization::BACKEND_ADMINISTRATOR,
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

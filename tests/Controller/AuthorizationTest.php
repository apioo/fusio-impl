<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Tests\Controller;

use Fusio\Impl\Tests\DbTestCase;

/**
 * AuthorizationTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AuthorizationTest extends DbTestCase
{
    public function testPublic()
    {
        $response = $this->sendRequest('/foo', 'GET', [
            'User-Agent' => 'Fusio TestCase',
        ]);

        $body = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "itemsPerPage": 16,
    "startIndex": 0,
    "entry": [
        {
            "id": 2,
            "title": "bar",
            "content": "foo",
            "date": "2015-02-27T19:59:15+00:00"
        },
        {
            "id": 1,
            "title": "foo",
            "content": "bar",
            "date": "2015-02-27T19:59:15+00:00"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Remaining'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPublicWithAuthorization()
    {
        $response = $this->sendRequest('/foo', 'GET', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $body = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "itemsPerPage": 16,
    "startIndex": 0,
    "entry": [
        {
            "id": 2,
            "title": "bar",
            "content": "foo",
            "date": "2015-02-27T19:59:15+00:00"
        },
        {
            "id": 1,
            "title": "foo",
            "content": "bar",
            "date": "2015-02-27T19:59:15+00:00"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Remaining'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPublicWithInvalidAuthorization()
    {
        $response = $this->sendRequest('/foo', 'GET', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer 1234'
        ]);

        $body = (string)$response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertEquals('Bearer realm="Fusio"', $response->getHeader('WWW-Authenticate'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Invalid access token', $data->message);
    }

    public function testPublicWithEmptyAuthorization()
    {
        $response = $this->sendRequest('/foo', 'GET', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => ''
        ]);

        $body = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "itemsPerPage": 16,
    "startIndex": 0,
    "entry": [
        {
            "id": 2,
            "title": "bar",
            "content": "foo",
            "date": "2015-02-27T19:59:15+00:00"
        },
        {
            "id": 1,
            "title": "foo",
            "content": "bar",
            "date": "2015-02-27T19:59:15+00:00"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Remaining'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testNotPublic()
    {
        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $response = $this->sendRequest('/foo', 'POST', [
            'User-Agent' => 'Fusio TestCase',
        ], $body);

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertEquals('Bearer realm="Fusio"', $response->getHeader('WWW-Authenticate'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.createFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('stable', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Missing authorization header', $data->message);
    }

    public function testNotPublicWithAuthorization()
    {
        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $response = $this->sendRequest('/foo', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ], $body);

        $body = (string)$response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Entry successfully created",
    "id": "3"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertEquals('16', $response->getHeader('RateLimit-Limit'), $body);
        $this->assertEquals('16', $response->getHeader('RateLimit-Remaining'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.createFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('stable', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testNotPublicWithInvalidAuthorization()
    {
        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $response = $this->sendRequest('/foo', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer 1234'
        ], $body);

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertEquals('Bearer realm="Fusio"', $response->getHeader('WWW-Authenticate'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.createFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('stable', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Invalid access token', $data->message);
    }

    public function testNotPublicWithEmptyAuthorization()
    {
        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $response = $this->sendRequest('/foo', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => ''
        ], $body);

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertEquals('Bearer realm="Fusio"', $response->getHeader('WWW-Authenticate'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.createFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('stable', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Missing authorization header', $data->message);
    }
}

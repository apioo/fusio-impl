<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Tests\System\Api;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Json\Rpc\Builder;

/**
 * JsonRpcTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class JsonRpcTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/system/jsonrpc', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/json_rpc.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/system/jsonrpc', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $builder = new Builder();
        $message = $builder->createCall('listFoo', [], 1);

        $response = $this->sendRequest('/system/jsonrpc', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ), \json_encode($message));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "jsonrpc": "2.0",
    "id": 1,
    "result": {
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
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPostBatch()
    {
        $builder = new Builder();
        $message = [
            $builder->createCall('listFoo', [], 1),
            $builder->createCall('listFoo', ['filterBy' => 'id', 'filterOp' => 'equals', 'filterValue' => 1], 2),
        ];

        $response = $this->sendRequest('/system/jsonrpc', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ), \json_encode($message));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
[
    {
        "jsonrpc": "2.0",
        "id": 1,
        "result": {
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
    },
    {
        "jsonrpc": "2.0",
        "id": 2,
        "result": {
            "totalResults": 2,
            "itemsPerPage": 16,
            "startIndex": 0,
            "entry": [
                {
                    "id": 1,
                    "title": "foo",
                    "content": "bar",
                    "date": "2015-02-27T19:59:15+00:00"
                }
            ]
        }
    }
]
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPostMissingAuthorization()
    {
        $builder = new Builder();
        $message = $builder->createCall('createFoo', ['payload' => (object) []], 1);

        $response = $this->sendRequest('/system/jsonrpc', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), \json_encode($message));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "jsonrpc": "2.0",
    "id": 1,
    "error": {
        "code": 401,
        "message": "Missing authorization header"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPostEmptyBody()
    {
        $builder = new Builder();
        $message = $builder->createCall('createFoo', ['payload' => (object) []], 1);

        $response = $this->sendRequest('/system/jsonrpc', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ), \json_encode($message));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "jsonrpc": "2.0",
    "error": {
        "code": 400,
        "message": "Property title must not be null"
    },
    "id": 1
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPostInvalidBody()
    {
        $builder = new Builder();
        $message = $builder->createCall('createFoo', ['payload' => ['title' => 12]], 1);

        $response = $this->sendRequest('/system/jsonrpc', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ), \json_encode($message));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "jsonrpc": "2.0",
    "id": 1,
    "error": {
        "code": 400,
        "message": "\/title must be of type string"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/system/jsonrpc', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/system/jsonrpc', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

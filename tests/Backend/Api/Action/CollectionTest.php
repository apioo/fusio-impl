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

use Fusio\Adapter\Util\Action\UtilStaticResponse;
use Fusio\Impl\Backend;
use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Normalizer;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/backend/action', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "totalResults": 5,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 5,
            "status": 1,
            "name": "MIME-Action",
            "class": "Fusio.Impl.Tests.Adapter.Test.MimeAction",
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 1,
            "name": "Inspect-Action",
            "class": "Fusio.Impl.Tests.Adapter.Test.InspectAction",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "status": 1,
            "name": "Sql-Insert",
            "class": "Fusio.Adapter.Sql.Action.SqlInsert",
            "date": "[datetime]"
        },
        {
            "id": 2,
            "status": 1,
            "name": "Sql-Select-All",
            "class": "Fusio.Adapter.Sql.Action.SqlSelectAll",
            "date": "[datetime]"
        },
        {
            "id": 1,
            "status": 1,
            "name": "Util-Static-Response",
            "class": "Fusio.Adapter.Util.Action.UtilStaticResponse",
            "metadata": {
                "foo": "bar"
            },
            "date": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/action?search=Sql', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "totalResults": 2,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 3,
            "status": 1,
            "name": "Sql-Insert",
            "class": "Fusio.Adapter.Sql.Action.SqlInsert",
            "date": "[datetime]"
        },
        {
            "id": 2,
            "status": 1,
            "name": "Sql-Select-All",
            "class": "Fusio.Adapter.Sql.Action.SqlSelectAll",
            "date": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/action?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "totalResults": 5,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 5,
            "status": 1,
            "name": "MIME-Action",
            "class": "Fusio.Impl.Tests.Adapter.Test.MimeAction",
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 1,
            "name": "Inspect-Action",
            "class": "Fusio.Impl.Tests.Adapter.Test.InspectAction",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "status": 1,
            "name": "Sql-Insert",
            "class": "Fusio.Adapter.Sql.Action.SqlInsert",
            "date": "[datetime]"
        },
        {
            "id": 2,
            "status": 1,
            "name": "Sql-Select-All",
            "class": "Fusio.Adapter.Sql.Action.SqlSelectAll",
            "date": "[datetime]"
        },
        {
            "id": 1,
            "status": 1,
            "name": "Util-Static-Response",
            "class": "Fusio.Adapter.Util.Action.UtilStaticResponse",
            "metadata": {
                "foo": "bar"
            },
            "date": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetUnauthorized()
    {
        $response = $this->sendRequest('/backend/action', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertEquals(false, $data->success, $body);
        $this->assertStringStartsWith('Missing authorization header', $data->message, $body);
    }

    public function testPost()
    {
        $config = [
            'string' => 'foo',
            'integer' => 12,
            'number' => 12.34,
            'boolean' => true,
            'null' => null,
            'array' => ['foo', 12, 12.34, true, null],
        ];

        $metadata = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/action', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'     => 'Foo',
            'class'    => UtilStaticResponse::class,
            'config'   => $config,
            'metadata' => $metadata
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Action successfully created",
    "id": "6"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertAction($this->connection, 'Foo', UtilStaticResponse::class, json_encode(array_filter($config)), $metadata);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/action', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/action', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

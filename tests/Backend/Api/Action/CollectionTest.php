<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Backend;
use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

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
            "id": 180,
            "status": 1,
            "name": "Inspect-Action",
            "date": "[datetime]"
        },
        {
            "id": 179,
            "status": 1,
            "name": "Sql-Insert",
            "date": "[datetime]"
        },
        {
            "id": 178,
            "status": 1,
            "name": "Sql-Select-All",
            "date": "[datetime]"
        },
        {
            "id": 177,
            "status": 1,
            "name": "Util-Static-Response",
            "metadata": {
                "foo": "bar"
            },
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 1,
            "name": "System_Action_Meta_GetAbout",
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
            "id": 179,
            "status": 1,
            "name": "Sql-Insert",
            "date": "[datetime]"
        },
        {
            "id": 178,
            "status": 1,
            "name": "Sql-Select-All",
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
            "id": 180,
            "status": 1,
            "name": "Inspect-Action",
            "date": "[datetime]"
        },
        {
            "id": 179,
            "status": 1,
            "name": "Sql-Insert",
            "date": "[datetime]"
        },
        {
            "id": 178,
            "status": 1,
            "name": "Sql-Select-All",
            "date": "[datetime]"
        },
        {
            "id": 177,
            "status": 1,
            "name": "Util-Static-Response",
            "metadata": {
                "foo": "bar"
            },
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 1,
            "name": "System_Action_Meta_GetAbout",
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
            'engine'   => PhpClass::class,
            'config'   => $config,
            'metadata' => $metadata
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Action successfully created"
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

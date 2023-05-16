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

namespace Fusio\Impl\Tests\Backend\Api\Operation;

use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
        $response = $this->sendRequest('/backend/operation', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 8,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 178,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "DELETE",
            "httpPath": "\/inspect\/:foo",
            "name": "inspect.delete",
            "action": ""
        },
        {
            "id": 177,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "PATCH",
            "httpPath": "\/inspect\/:foo",
            "name": "inspect.patch",
            "action": ""
        },
        {
            "id": 176,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "PUT",
            "httpPath": "\/inspect\/:foo",
            "name": "inspect.put",
            "action": ""
        },
        {
            "id": 175,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/inspect\/:foo",
            "name": "inspect.post",
            "action": ""
        },
        {
            "id": 174,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "GET",
            "httpPath": "\/inspect\/:foo",
            "name": "inspect.get",
            "action": ""
        },
        {
            "id": 173,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/foo",
            "name": "test.createFoo",
            "action": ""
        },
        {
            "id": 172,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "GET",
            "httpPath": "\/foo",
            "name": "test.listFoo",
            "action": ""
        },
        {
            "id": 1,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "GET",
            "httpPath": "\/",
            "name": "getAbout",
            "action": "System_Action_GetAbout"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/operation?search=inspec', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 5,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 178,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "DELETE",
            "httpPath": "\/inspect\/:foo",
            "name": "inspect.delete",
            "action": ""
        },
        {
            "id": 177,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "PATCH",
            "httpPath": "\/inspect\/:foo",
            "name": "inspect.patch",
            "action": ""
        },
        {
            "id": 176,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "PUT",
            "httpPath": "\/inspect\/:foo",
            "name": "inspect.put",
            "action": ""
        },
        {
            "id": 175,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "POST",
            "httpPath": "\/inspect\/:foo",
            "name": "inspect.post",
            "action": ""
        },
        {
            "id": 174,
            "status": 1,
            "active": 1,
            "public": 0,
            "stability": 2,
            "httpMethod": "GET",
            "httpPath": "\/inspect\/:foo",
            "name": "inspect.get",
            "action": ""
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/operation?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 3,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 117,
            "status": 1,
            "path": "\/inspect\/:foo",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController"
        },
        {
            "id": 116,
            "status": 1,
            "path": "\/foo",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController"
        },
        {
            "id": 7,
            "status": 1,
            "path": "\/",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $metadata = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/operation', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path'     => '/bar',
            'scopes'   => ['foo', 'baz'],
            'config'   => [[
                'version' => 1,
                'status'  => 4,
                'methods' => [
                    'GET' => [
                        'active'      => true,
                        'public'      => true,
                        'description' => 'The GET method',
                        'parameters'  => 'Collection-Schema',
                        'responses'   => [
                            '200'     => 'Passthru'
                        ],
                        'action'      => 'Sql-Table',
                    ],
                    'POST' => [
                        'active'      => true,
                        'public'      => true,
                        'description' => 'The POST method',
                        'request'     => 'Collection-Schema',
                        'responses'   => [
                            '201'     => 'Passthru'
                        ],
                        'action'      => 'Sql-Table',
                    ]
                ],
            ]],
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Route successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertOperation('/bar', ['foo', 'baz'], [[
            'method'       => 'GET',
            'version'      => 1,
            'status'       => 4,
            'active'       => true,
            'public'       => true,
            'description'  => 'The GET method',
            'operation_id' => 'get.bar',
            'parameters'   => 'Collection-Schema',
            'request'      => null,
            'responses'    => [
                '200'      => 'Passthru'
            ],
            'action'       => 'Sql-Table',
            'costs'        => 0,
        ], [
            'method'       => 'POST',
            'version'      => 1,
            'status'       => 4,
            'active'       => true,
            'public'       => true,
            'description'  => 'The POST method',
            'operation_id' => 'post.bar',
            'parameters'   => null,
            'request'      => 'Collection-Schema',
            'responses'    => [
                '201'      => 'Passthru'
            ],
            'action'       => 'Sql-Table',
            'costs'        => 0,
        ]], $metadata);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/operation', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/operation', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

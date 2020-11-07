<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\Api\Route;

use Fusio\Adapter\Sql\Action\SqlInsert;
use Fusio\Adapter\Sql\Action\SqlSelectAll;
use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * ProviderTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ProviderTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/routes/provider/testprovider', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/provider.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/routes/provider/testprovider', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "element": [
        {
            "element": "http:\/\/fusio-project.org\/ns\/2015\/form\/input",
            "type": "text",
            "name": "table",
            "title": "Table"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/routes/provider/testprovider', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path' => '/foo',
            'scopes' => ['foo'],
            'config' => [
                'table' => 'foobar'
            ],
        ]));

        $body = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Provider successful created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check schema
        $schema = <<<JSON
{
  "type": "object",
  "properties": {
    "title": {
      "type": "string"
    },
    "createDate": {
      "type": "string",
      "format": "date-time"
    }
  }
}
JSON;

        Assert::assertSchema('Provider_Schema_Request', $schema);

        $schema = <<<JSON
{
  "type": "object",
  "properties": {
    "title": {
      "type": "string"
    },
    "createDate": {
      "type": "string",
      "format": "date-time"
    }
  }
}
JSON;

        Assert::assertSchema('Provider_Schema_Response', $schema);

        // check action
        Assert::assertAction('Provider_Action_Select', SqlSelectAll::class, '{"table":"foobar"}');
        Assert::assertAction('Provider_Action_Insert', SqlInsert::class, '{"table":"foobar"}');

        // check routes
        Assert::assertRoute('/foo/table', ['foo', 'foo', 'bar'], [[
            'method'       => 'GET',
            'version'      => 1,
            'status'       => Resource::STATUS_DEVELOPMENT,
            'active'       => true,
            'public'       => true,
            'description'  => 'Returns all entries on the table',
            'operation_id' => 'get.foo.table',
            'parameters'   => null,
            'request'      => 'Provider_Schema_Request',
            'responses'    => [
                '200'      => 'Provider_Schema_Response'
            ],
            'action'       => 'Provider_Action_Select',
            'costs'        => 0,
        ], [
            'method'       => 'POST',
            'version'      => 1,
            'status'       => Resource::STATUS_DEVELOPMENT,
            'active'       => true,
            'public'       => false,
            'description'  => 'Creates a new entry on the table',
            'operation_id' => 'post.foo.table',
            'parameters'   => null,
            'request'      => 'Provider_Schema_Request',
            'responses'    => [
                '200'      => 'Provider_Schema_Response'
            ],
            'action'       => 'Provider_Action_Insert',
            'costs'        => 0,
        ]]);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/routes/provider/testprovider', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'table' => 'foobar'
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "schemas": [
        {
            "name": "Provider_Schema_Request",
            "source": {
                "type": "object",
                "properties": {
                    "title": {
                        "type": "string"
                    },
                    "createDate": {
                        "type": "string",
                        "format": "date-time"
                    }
                }
            }
        },
        {
            "name": "Provider_Schema_Response",
            "source": {
                "type": "object",
                "properties": {
                    "title": {
                        "type": "string"
                    },
                    "createDate": {
                        "type": "string",
                        "format": "date-time"
                    }
                }
            }
        }
    ],
    "actions": [
        {
            "name": "Provider_Action_Select",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlSelectAll",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "table": "foobar"
            }
        },
        {
            "name": "Provider_Action_Insert",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlInsert",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "table": "foobar"
            }
        }
    ],
    "routes": [
        {
            "priority": 1,
            "path": "\/table",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController",
            "scopes": [
                "foo",
                "bar"
            ],
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "description": "Returns all entries on the table",
                            "request": 0,
                            "responses": {
                                "200": 1
                            },
                            "action": 0
                        },
                        "POST": {
                            "active": true,
                            "public": false,
                            "description": "Creates a new entry on the table",
                            "request": 0,
                            "responses": {
                                "200": 1
                            },
                            "action": 1
                        }
                    }
                }
            ]
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/routes/provider/testprovider', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

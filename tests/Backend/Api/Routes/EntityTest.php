<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\Api\Routes;

use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Table\Routes as TableRoutes;
use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/routes/' . (Fixture::getLastRouteId() + 1), 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/routes\/$route_id<[0-9]+>",
    "version": "*",
    "status": 1,
    "description": null,
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "path-template": {
                "type": "object",
                "title": "path",
                "properties": {
                    "route_id": {
                        "type": "integer"
                    }
                }
            },
            "Routes_Version": {
                "type": "object",
                "title": "Routes Version",
                "properties": {
                    "version": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "methods": {
                        "$ref": "#\/definitions\/Routes_Methods"
                    }
                }
            },
            "Routes_Methods": {
                "type": "object",
                "title": "Routes Methods",
                "patternProperties": {
                    "^(GET|POST|PUT|PATCH|DELETE)$": {
                        "$ref": "#\/definitions\/Routes_Method"
                    }
                }
            },
            "Routes_Method": {
                "type": "object",
                "title": "Routes Method",
                "properties": {
                    "method": {
                        "type": "string"
                    },
                    "version": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "active": {
                        "type": "boolean"
                    },
                    "public": {
                        "type": "boolean"
                    },
                    "description": {
                        "type": "string"
                    },
                    "operationId": {
                        "type": "string"
                    },
                    "parameters": {
                        "type": "integer"
                    },
                    "request": {
                        "type": "integer"
                    },
                    "response": {
                        "type": "integer"
                    },
                    "responses": {
                        "$ref": "#\/definitions\/Routes_Method_Responses"
                    },
                    "action": {
                        "type": "integer"
                    },
                    "costs": {
                        "type": "integer"
                    }
                }
            },
            "Routes_Method_Responses": {
                "type": "object",
                "title": "Routes Method Responses",
                "patternProperties": {
                    "^([0-9]{3})$": {
                        "type": "integer"
                    }
                }
            },
            "Routes": {
                "type": "object",
                "title": "Routes",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "priority": {
                        "type": "integer"
                    },
                    "path": {
                        "type": "string"
                    },
                    "controller": {
                        "type": "string"
                    },
                    "scopes": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    },
                    "config": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Routes_Version"
                        }
                    }
                },
                "required": [
                    "config"
                ]
            },
            "Message": {
                "type": "object",
                "title": "Message",
                "properties": {
                    "success": {
                        "type": "boolean"
                    },
                    "message": {
                        "type": "string"
                    }
                }
            },
            "GET-200-response": {
                "$ref": "#\/definitions\/Routes"
            },
            "PUT-request": {
                "$ref": "#\/definitions\/Routes"
            },
            "PUT-200-response": {
                "$ref": "#\/definitions\/Message"
            },
            "DELETE-200-response": {
                "$ref": "#\/definitions\/Message"
            }
        }
    },
    "pathParameters": "#\/definitions\/path-template",
    "methods": {
        "GET": {
            "responses": {
                "200": "#\/definitions\/GET-200-response"
            }
        },
        "PUT": {
            "request": "#\/definitions\/PUT-request",
            "responses": {
                "200": "#\/definitions\/PUT-200-response"
            }
        },
        "DELETE": {
            "responses": {
                "200": "#\/definitions\/DELETE-200-response"
            }
        }
    },
    "links": [
        {
            "rel": "openapi",
            "href": "\/export\/openapi\/*\/backend\/routes\/$route_id<[0-9]+>"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/routes\/$route_id<[0-9]+>"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/routes\/$route_id<[0-9]+>"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/routes/' . (Fixture::getLastRouteId() + 1), 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $id     = Fixture::getLastRouteId() + 1;
        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": {$id},
    "status": 1,
    "path": "\/foo",
    "controller": "Fusio\\\\Impl\\\\Controller\\\\SchemaApiController",
    "scopes": [
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
                    "operationId": "listFoo",
                    "responses": {
                        "200": 2
                    },
                    "action": 3
                },
                "POST": {
                    "active": true,
                    "public": false,
                    "operationId": "createFoo",
                    "request": 3,
                    "responses": {
                        "201": 1
                    },
                    "action": 3,
                    "costs": 1
                }
            }
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/backend/routes/1000', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Could not find route"
}
JSON;

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/routes/' . (Fixture::getLastRouteId() + 1), 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/routes/' . (Fixture::getLastRouteId() + 1), 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path'   => '/foo',
            'scopes' => ['foo', 'baz'],
            'config' => [[
                'version' => 1,
                'status'  => 4,
                'methods' => [
                    'GET' => [
                        'active'     => true,
                        'public'     => true,
                        'parameters' => 2,
                        'action'     => 3,
                        'responses'  => [
                            '200'    => 1
                        ],
                        'costs'      => 16,
                    ],
                    'POST' => [
                        'active'     => true,
                        'public'     => false,
                        'action'     => 3,
                        'request'    => 1,
                        'responses'  => [
                            '201'    => 1
                        ],
                    ],
                ],
            ]],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Routes successful updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertRoute('/foo', ['foo', 'baz'], [[
            'method'       => 'GET',
            'version'      => 1,
            'status'       => 4,
            'active'       => true,
            'public'       => true,
            'description'  => '',
            'operation_id' => 'get.foo',
            'parameters'   => 'Collection-Schema',
            'request'      => null,
            'responses'    => [
                '200'      => 'Passthru'
            ],
            'action'       => 'Sql-Table',
            'costs'        => 16,
        ], [
            'method'       => 'POST',
            'version'      => 1,
            'status'       => 4,
            'active'       => true,
            'public'       => false,
            'description'  => '',
            'operation_id' => 'post.foo',
            'parameters'   => null,
            'request'      => 'Passthru',
            'responses'    => [
                '201'      => 'Passthru'
            ],
            'action'       => 'Sql-Table',
            'costs'        => 0,
        ]]);
    }

    public function testPutDeploy()
    {
        $response = $this->sendRequest('/backend/routes/' . (Fixture::getLastRouteId() + 1), 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path'   => '/foo',
            'config' => [[
                'version' => 1,
                'status'  => Resource::STATUS_ACTIVE,
            ]],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Routes successful updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertRoute('/foo', ['bar'], [[
            'method'       => 'GET',
            'version'      => 1,
            'status'       => 1,
            'active'       => true,
            'public'       => true,
            'description'  => '',
            'operation_id' => 'listFoo',
            'parameters'   => null,
            'request'      => null,
            'responses'    => [
                '200'      => 'Collection-Schema'
            ],
            'action'       => 'Sql-Table',
            'costs'        => 0,
        ], [
            'method'       => 'POST',
            'version'      => 1,
            'status'       => 1,
            'active'       => true,
            'public'       => false,
            'description'  => '',
            'operation_id' => 'createFoo',
            'parameters'   => null,
            'request'      => 'Entry-Schema',
            'responses'    => [
                '201'      => 'Passthru'
            ],
            'action'       => 'Sql-Table',
            'costs'        => 1,
        ], [
            'method'       => 'PUT',
            'version'      => 1,
            'status'       => 1,
            'active'       => false,
            'public'       => false,
            'description'  => '',
            'operation_id' => '',
            'parameters'   => null,
            'request'      => null,
            'responses'    => [],
            'action'       => null,
            'costs'        => 0,
        ], [
            'method'       => 'PATCH',
            'version'      => 1,
            'status'       => 1,
            'active'       => false,
            'public'       => false,
            'description'  => '',
            'operation_id' => '',
            'parameters'   => null,
            'request'      => null,
            'responses'    => [],
            'action'       => null,
            'costs'        => 0,
        ], [
            'method'       => 'DELETE',
            'version'      => 1,
            'status'       => 1,
            'active'       => false,
            'public'       => false,
            'description'  => '',
            'operation_id' => '',
            'parameters'   => null,
            'request'      => null,
            'responses'    => [],
            'action'       => null,
            'costs'        => 0,
        ]]);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/routes/' . (Fixture::getLastRouteId() + 1), 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Routes successful deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_routes')
            ->where('id = ' . (Fixture::getLastRouteId() + 1))
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(Fixture::getLastRouteId() + 1, $row['id']);
        $this->assertEquals(TableRoutes::STATUS_DELETED, $row['status']);
    }
}

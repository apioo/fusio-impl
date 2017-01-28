<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Table\Routes as TableRoutes;
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
        $response = $this->sendRequest('http://127.0.0.1/doc/*/backend/routes/63', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/routes\/:route_id",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "Version": {
                "type": "object",
                "title": "version",
                "properties": {
                    "version": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "methods": {
                        "$ref": "#\/definitions\/Methods"
                    }
                }
            },
            "Methods": {
                "type": "object",
                "title": "methods",
                "patternProperties": {
                    "^(GET|POST|PUT|DELETE)$": {
                        "$ref": "#\/definitions\/Method"
                    }
                }
            },
            "Method": {
                "type": "object",
                "title": "method",
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
                    "request": {
                        "type": "integer"
                    },
                    "response": {
                        "type": "integer"
                    },
                    "action": {
                        "type": "integer"
                    }
                }
            },
            "Routes": {
                "type": "object",
                "title": "routes",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "path": {
                        "type": "string"
                    },
                    "config": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Version"
                        }
                    }
                },
                "required": [
                    "config"
                ]
            },
            "Message": {
                "type": "object",
                "title": "message",
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
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/routes\/:route_id"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/routes\/:route_id"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/routes/63', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "id": 63,
    "status": 1,
    "path": "\/foo",
    "controller": "Fusio\\Impl\\Controller\\SchemaApiController",
    "config": [
        {
            "version": 1,
            "status": 4,
            "methods": {
                "GET": {
                    "active": true,
                    "public": true,
                    "response": 2,
                    "action": 3
                },
                "POST": {
                    "active": true,
                    "public": false,
                    "request": 1,
                    "response": 1,
                    "action": 3
                }
            }
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/routes/63', 'POST', array(
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
        $response = $this->sendRequest('http://127.0.0.1/backend/routes/63', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path'   => '/foo',
            'config' => [[
                'version' => 1,
                'status'  => 4,
                'methods' => [
                    'GET' => [
                        'active'   => true,
                        'public'   => true,
                        'action'   => 3,
                        'response' => 1,
                    ],
                    'POST' => [
                        'active'   => true,
                        'public'   => false,
                        'action'   => 3,
                        'request'  => 1,
                        'response' => 1,
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
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'methods', 'path', 'controller')
            ->from('fusio_routes')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(63, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('GET|POST|PUT|DELETE', $row['methods']);
        $this->assertEquals('/foo', $row['path']);
        $this->assertEquals('Fusio\Impl\Controller\SchemaApiController', $row['controller']);

        // check methods
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'routeId', 'method', 'version', 'status', 'active', 'public', 'request', 'response', 'action')
            ->from('fusio_routes_method')
            ->where('routeId = :routeId')
            ->orderBy('id', 'ASC')
            ->setFirstResult(0)
            ->getSQL();

        $result = Environment::getService('connection')->fetchAll($sql, ['routeId' => $row['id']]);

        $this->assertEquals(2, count($result));

        $this->assertEquals('GET', $result[0]['method']);
        $this->assertEquals(1, $result[0]['version']);
        $this->assertEquals(4, $result[0]['status']);
        $this->assertEquals(1, $result[0]['active']);
        $this->assertEquals(1, $result[0]['public']);
        $this->assertEquals(null, $result[0]['request']);
        $this->assertEquals(1, $result[0]['response']);
        $this->assertEquals(3, $result[0]['action']);

        $this->assertEquals('POST', $result[1]['method']);
        $this->assertEquals(1, $result[1]['version']);
        $this->assertEquals(4, $result[1]['status']);
        $this->assertEquals(1, $result[1]['active']);
        $this->assertEquals(0, $result[1]['public']);
        $this->assertEquals(1, $result[1]['request']);
        $this->assertEquals(1, $result[1]['response']);
        $this->assertEquals(3, $result[1]['action']);
    }

    public function testPutDeploy()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/routes/63', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path'   => '/foo',
            'config' => [[
                'version' => 1,
                'status'  => Resource::STATUS_ACTIVE,
                'methods' => [
                    'GET' => [
                        'active'   => true,
                        'public'   => true,
                        'action'   => 3,
                        'response' => 1,
                    ],
                    'POST' => [
                        'active'   => true,
                        'public'   => false,
                        'action'   => 3,
                        'request'  => 1,
                        'response' => 1,
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
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'methods', 'path', 'controller')
            ->from('fusio_routes')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(63, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('GET|POST|PUT|DELETE', $row['methods']);
        $this->assertEquals('/foo', $row['path']);
        $this->assertEquals('Fusio\Impl\Controller\SchemaApiController', $row['controller']);

        // check methods
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'routeId', 'method', 'version', 'status', 'active', 'public', 'request', 'requestCache', 'response', 'responseCache', 'action', 'actionCache')
            ->from('fusio_routes_method')
            ->where('routeId = :routeId')
            ->orderBy('id', 'ASC')
            ->getSQL();

        $result = Environment::getService('connection')->fetchAll($sql, ['routeId' => $row['id']]);

        $this->assertEquals(2, count($result));

        $this->assertEquals('GET', $result[0]['method']);
        $this->assertEquals(1, $result[0]['version']);
        $this->assertEquals(Resource::STATUS_ACTIVE, $result[0]['status']);
        $this->assertEquals(1, $result[0]['active']);
        $this->assertEquals(1, $result[0]['public']);
        $this->assertEquals(null, $result[0]['request']);
        $this->assertEmpty($result[0]['requestCache']);
        $this->assertEquals(2, $result[0]['response']);
        $this->assertNotEmpty($result[0]['responseCache']);
        $this->assertEquals(3, $result[0]['action']);
        $this->assertNotEmpty($result[0]['actionCache']);

        $this->assertEquals('POST', $result[1]['method']);
        $this->assertEquals(1, $result[1]['version']);
        $this->assertEquals(Resource::STATUS_ACTIVE, $result[1]['status']);
        $this->assertEquals(1, $result[1]['active']);
        $this->assertEquals(0, $result[1]['public']);
        $this->assertEquals(1, $result[1]['request']);
        $this->assertNotEmpty($result[1]['requestCache']);
        $this->assertEquals(1, $result[1]['response']);
        $this->assertNotEmpty($result[1]['responseCache']);
        $this->assertEquals(3, $result[1]['action']);
        $this->assertNotEmpty($result[1]['actionCache']);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/routes/63', 'DELETE', array(
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
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(63, $row['id']);
        $this->assertEquals(TableRoutes::STATUS_DELETED, $row['status']);
    }
}

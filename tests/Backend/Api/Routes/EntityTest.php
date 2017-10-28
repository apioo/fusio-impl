<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Table\Routes as TableRoutes;
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
                    "scopes": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
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
                    "^(GET|POST|PUT|PATCH|DELETE)$": {
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
                        "$ref": "#\/definitions\/Responses"
                    },
                    "action": {
                        "type": "integer"
                    }
                }
            },
            "Responses": {
                "type": "object",
                "title": "responses",
                "patternProperties": {
                    "^([0-9]{3})$": {
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
    "config": [
        {
            "version": 1,
            "status": 4,
            "methods": {
                "GET": {
                    "active": true,
                    "public": true,
                    "responses": {
                        "200": 2
                    },
                    "action": 3
                },
                "POST": {
                    "active": true,
                    "public": false,
                    "request": 3,
                    "responses": {
                        "201": 1
                    },
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

    public function testGetNotFound()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/backend/routes/100', 'GET', array(
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
            'config' => [[
                'version' => 1,
                'status'  => 4,
                'scopes'  => ['foo', 'baz'],
                'methods' => [
                    'GET' => [
                        'active'     => true,
                        'public'     => true,
                        'parameters' => 2,
                        'action'     => 3,
                        'responses'  => [
                            '200'    => 1
                        ],
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
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'methods', 'path', 'controller')
            ->from('fusio_routes')
            ->where('id = ' . (Fixture::getLastRouteId() + 1))
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(Fixture::getLastRouteId() + 1, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('ANY', $row['methods']);
        $this->assertEquals('/foo', $row['path']);
        $this->assertEquals('Fusio\Impl\Controller\SchemaApiController', $row['controller']);

        // check methods
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'routeId', 'method', 'version', 'status', 'active', 'public', 'parameters', 'request', 'action')
            ->from('fusio_routes_method')
            ->where('routeId = :routeId')
            ->orderBy('id', 'ASC')
            ->setFirstResult(0)
            ->getSQL();

        $methods = Environment::getService('connection')->fetchAll($sql, ['routeId' => $row['id']]);

        $this->assertEquals(2, count($methods));

        $this->assertEquals('GET', $methods[0]['method']);
        $this->assertEquals(1, $methods[0]['version']);
        $this->assertEquals(4, $methods[0]['status']);
        $this->assertEquals(1, $methods[0]['active']);
        $this->assertEquals(1, $methods[0]['public']);
        $this->assertEquals(2, $methods[0]['parameters']);
        $this->assertEquals(null, $methods[0]['request']);
        $this->assertEquals(3, $methods[0]['action']);

        // check responses
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'methodId', 'code', 'response')
            ->from('fusio_routes_response')
            ->where('methodId = :methodId')
            ->orderBy('id', 'ASC')
            ->setFirstResult(0)
            ->getSQL();

        $responses = Environment::getService('connection')->fetchAll($sql, ['methodId' => $methods[0]['id']]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['response']);

        $this->assertEquals('POST', $methods[1]['method']);
        $this->assertEquals(1, $methods[1]['version']);
        $this->assertEquals(4, $methods[1]['status']);
        $this->assertEquals(1, $methods[1]['active']);
        $this->assertEquals(0, $methods[1]['public']);
        $this->assertEquals(null, $methods[1]['parameters']);
        $this->assertEquals(1, $methods[1]['request']);
        $this->assertEquals(3, $methods[1]['action']);

        // check responses
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'methodId', 'code', 'response')
            ->from('fusio_routes_response')
            ->where('methodId = :methodId')
            ->orderBy('id', 'ASC')
            ->setFirstResult(0)
            ->getSQL();

        $responses = Environment::getService('connection')->fetchAll($sql, ['methodId' => $methods[1]['id']]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(201, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['response']);

        // check scopes
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('s.name')
            ->from('fusio_scope_routes', 'r')
            ->innerJoin('r', 'fusio_scope', 's', 's.id = r.scopeId')
            ->where('r.routeId = :routeId')
            ->orderBy('s.id', 'ASC')
            ->setFirstResult(0)
            ->getSQL();

        $scopes = Environment::getService('connection')->fetchAll($sql, ['routeId' => $row['id']]);

        $this->assertEquals(2, count($scopes));
        $this->assertEquals('foo', $scopes[0]['name']);
        $this->assertEquals('baz', $scopes[1]['name']);
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
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'methods', 'path', 'controller')
            ->from('fusio_routes')
            ->where('id = ' . (Fixture::getLastRouteId() + 1))
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(Fixture::getLastRouteId() + 1, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('ANY', $row['methods']);
        $this->assertEquals('/foo', $row['path']);
        $this->assertEquals('Fusio\Impl\Controller\SchemaApiController', $row['controller']);

        // check methods
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'routeId', 'method', 'version', 'status', 'active', 'public', 'parameters', 'request', 'action', 'schemaCache', 'actionCache')
            ->from('fusio_routes_method')
            ->where('routeId = :routeId')
            ->orderBy('id', 'ASC')
            ->setFirstResult(0)
            ->getSQL();

        $methods = Environment::getService('connection')->fetchAll($sql, ['routeId' => $row['id']]);

        $this->assertEquals(5, count($methods));

        $this->assertEquals('GET', $methods[0]['method']);
        $this->assertEquals(1, $methods[0]['version']);
        $this->assertEquals(1, $methods[0]['status']);
        $this->assertEquals(1, $methods[0]['active']);
        $this->assertEquals(1, $methods[0]['public']);
        $this->assertEquals(null, $methods[0]['parameters']);
        $this->assertEquals(null, $methods[0]['request']);
        $this->assertEquals(3, $methods[0]['action']);

        // check responses
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'methodId', 'code', 'response')
            ->from('fusio_routes_response')
            ->where('methodId = :methodId')
            ->orderBy('id', 'ASC')
            ->setFirstResult(0)
            ->getSQL();

        $responses = Environment::getService('connection')->fetchAll($sql, ['methodId' => $methods[0]['id']]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(200, $responses[0]['code']);
        $this->assertEquals(2, $responses[0]['response']);

        $this->assertEquals('POST', $methods[1]['method']);
        $this->assertEquals(1, $methods[1]['version']);
        $this->assertEquals(1, $methods[1]['status']);
        $this->assertEquals(1, $methods[1]['active']);
        $this->assertEquals(0, $methods[1]['public']);
        $this->assertEquals(null, $methods[1]['parameters']);
        $this->assertEquals(3, $methods[1]['request']);
        $this->assertEquals(3, $methods[1]['action']);

        // check responses
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'methodId', 'code', 'response')
            ->from('fusio_routes_response')
            ->where('methodId = :methodId')
            ->orderBy('id', 'ASC')
            ->setFirstResult(0)
            ->getSQL();

        $responses = Environment::getService('connection')->fetchAll($sql, ['methodId' => $methods[1]['id']]);

        $this->assertEquals(1, count($responses));
        $this->assertEquals(201, $responses[0]['code']);
        $this->assertEquals(1, $responses[0]['response']);

        $this->assertEquals('PUT', $methods[2]['method']);
        $this->assertEquals(1, $methods[2]['version']);
        $this->assertEquals(1, $methods[2]['status']);
        $this->assertEquals(0, $methods[2]['active']);
        $this->assertEquals(0, $methods[2]['public']);
        $this->assertEquals(null, $methods[2]['parameters']);
        $this->assertEquals(null, $methods[2]['request']);
        $this->assertEquals(null, $methods[2]['action']);

        $this->assertEquals('PATCH', $methods[3]['method']);
        $this->assertEquals(1, $methods[3]['version']);
        $this->assertEquals(1, $methods[3]['status']);
        $this->assertEquals(0, $methods[3]['active']);
        $this->assertEquals(0, $methods[3]['public']);
        $this->assertEquals(null, $methods[3]['parameters']);
        $this->assertEquals(null, $methods[3]['request']);
        $this->assertEquals(null, $methods[3]['action']);

        $this->assertEquals('DELETE', $methods[4]['method']);
        $this->assertEquals(1, $methods[4]['version']);
        $this->assertEquals(1, $methods[4]['status']);
        $this->assertEquals(0, $methods[4]['active']);
        $this->assertEquals(0, $methods[4]['public']);
        $this->assertEquals(null, $methods[4]['parameters']);
        $this->assertEquals(null, $methods[4]['request']);
        $this->assertEquals(null, $methods[4]['action']);
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

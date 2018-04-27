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

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/routes', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/routes",
    "version": "*",
    "status": 1,
    "description": null,
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "GET-query": {
                "type": "object",
                "title": "query",
                "properties": {
                    "startIndex": {
                        "type": "integer"
                    },
                    "count": {
                        "type": "integer"
                    },
                    "search": {
                        "type": "string"
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
                            "$ref": "#\/definitions\/Version"
                        }
                    }
                },
                "required": [
                    "path",
                    "config"
                ]
            },
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
            "Collection": {
                "type": "object",
                "title": "collection",
                "properties": {
                    "totalResults": {
                        "type": "integer"
                    },
                    "startIndex": {
                        "type": "integer"
                    },
                    "entry": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Routes"
                        }
                    }
                }
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
                "$ref": "#\/definitions\/Collection"
            },
            "POST-request": {
                "$ref": "#\/definitions\/Routes"
            },
            "POST-201-response": {
                "$ref": "#\/definitions\/Message"
            }
        }
    },
    "methods": {
        "GET": {
            "queryParameters": "#\/definitions\/GET-query",
            "responses": {
                "200": "#\/definitions\/GET-200-response"
            }
        },
        "POST": {
            "request": "#\/definitions\/POST-request",
            "responses": {
                "201": "#\/definitions\/POST-201-response"
            }
        }
    },
    "links": [
        {
            "rel": "openapi",
            "href": "\/export\/openapi\/*\/backend\/routes"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/routes"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/routes"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/routes', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 3,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 74,
            "status": 1,
            "path": "\/inspect\/:foo",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController"
        },
        {
            "id": 73,
            "status": 1,
            "path": "\/foo",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController"
        },
        {
            "id": 72,
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

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/routes?search=inspec', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 74,
            "status": 1,
            "path": "\/inspect\/:foo",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/routes?count=80', 'GET', array(
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
            "id": 74,
            "status": 1,
            "path": "\/inspect\/:foo",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController"
        },
        {
            "id": 73,
            "status": 1,
            "path": "\/foo",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController"
        },
        {
            "id": 72,
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
        $response = $this->sendRequest('/backend/routes', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path'   => '/bar',
            'scopes' => ['foo', 'baz'],
            'config' => [[
                'version' => 1,
                'status'  => 4,
                'methods' => [
                    'GET' => [
                        'active'     => true,
                        'public'     => true,
                        'parameters' => 2,
                        'responses'  => [
                            '200'    => 1
                        ],
                        'action'     => 3,
                    ],
                    'POST' => [
                        'active'     => true,
                        'public'     => true,
                        'request'    => 2,
                        'responses'  => [
                            '201'    => 1
                        ],
                        'action'     => 3,
                    ]
                ],
            ]],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Route successful created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
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

        $this->assertEquals(Fixture::getLastRouteId() + 3, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('ANY', $row['methods']);
        $this->assertEquals('/bar', $row['path']);
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
        $this->assertEquals(1, $methods[1]['public']);
        $this->assertEquals(2, $methods[1]['request']);
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

    public function testPut()
    {
        $response = $this->sendRequest('/backend/routes', 'PUT', array(
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
        $response = $this->sendRequest('/backend/routes', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

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

namespace Fusio\Impl\Tests\Backend\Api\User;

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
        $response = $this->sendRequest('/doc/*/backend/user', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/user",
    "version": "*",
    "status": 1,
    "description": "",
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
            "User": {
                "type": "object",
                "title": "user",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string",
                        "pattern": "[a-zA-Z0-9\\-\\_\\.]{3,32}"
                    },
                    "email": {
                        "type": "string"
                    },
                    "scopes": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    },
                    "apps": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/App"
                        }
                    },
                    "date": {
                        "type": "string",
                        "format": "date-time"
                    },
                    "password": {
                        "type": "string"
                    }
                },
                "required": [
                    "status",
                    "name",
                    "email",
                    "password"
                ]
            },
            "App": {
                "type": "object",
                "title": "app",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "userId": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string",
                        "pattern": "[a-zA-Z0-9\\-\\_]{3,64}"
                    },
                    "url": {
                        "type": "string"
                    },
                    "parameters": {
                        "type": "string"
                    },
                    "appKey": {
                        "type": "string"
                    },
                    "appSecret": {
                        "type": "string"
                    },
                    "date": {
                        "type": "string",
                        "format": "date-time"
                    },
                    "scopes": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    },
                    "tokens": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Token"
                        }
                    }
                }
            },
            "Token": {
                "type": "object",
                "title": "token",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "token": {
                        "type": "string"
                    },
                    "scope": {
                        "type": "string"
                    },
                    "ip": {
                        "type": "string"
                    },
                    "expire": {
                        "type": "string",
                        "format": "date-time"
                    },
                    "date": {
                        "type": "string",
                        "format": "date-time"
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
                            "$ref": "#\/definitions\/User"
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
                "$ref": "#\/definitions\/User"
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
            "href": "\/export\/openapi\/*\/backend\/user"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/user"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/user"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/user', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = preg_replace('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/m', '[datetime]', $body);

        $expect = <<<'JSON'
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 4,
            "provider": 1,
            "status": 1,
            "name": "Developer",
            "email": "developer@localhost.com",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "provider": 1,
            "status": 2,
            "name": "Disabled",
            "email": "disabled@localhost.com",
            "date": "[datetime]"
        },
        {
            "id": 2,
            "provider": 1,
            "status": 0,
            "name": "Consumer",
            "email": "consumer@localhost.com",
            "date": "[datetime]"
        },
        {
            "id": 1,
            "provider": 1,
            "status": 1,
            "name": "Administrator",
            "email": "admin@localhost.com",
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
        $response = $this->sendRequest('/backend/user?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = preg_replace('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/m', '[datetime]', $body);

        $expect = <<<'JSON'
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 4,
            "provider": 1,
            "status": 1,
            "name": "Developer",
            "email": "developer@localhost.com",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "provider": 1,
            "status": 2,
            "name": "Disabled",
            "email": "disabled@localhost.com",
            "date": "[datetime]"
        },
        {
            "id": 2,
            "provider": 1,
            "status": 0,
            "name": "Consumer",
            "email": "consumer@localhost.com",
            "date": "[datetime]"
        },
        {
            "id": 1,
            "provider": 1,
            "status": 1,
            "name": "Administrator",
            "email": "admin@localhost.com",
            "date": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/user', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status'   => 0,
            'name'     => 'test',
            'email'    => 'test@localhost.com',
            'password' => 'fooo123!',
            'scopes'   => ['foo', 'bar'],
        ]));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "success": true,
    "message": "User successful created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'name', 'email', 'password')
            ->from('fusio_user')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(6, $row['id']);
        $this->assertEquals(0, $row['status']);
        $this->assertEquals('test', $row['name']);
        $this->assertEquals('test@localhost.com', $row['email']);
        $this->assertTrue(password_verify('fooo123!', $row['password']));

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'userId', 'scopeId')
            ->from('fusio_user_scope')
            ->where('userId = :userId')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $routes = Environment::getService('connection')->fetchAll($sql, ['userId' => 6]);

        $this->assertEquals([[
            'id'      => 15,
            'userId'  => 6,
            'scopeId' => 4,
        ], [
            'id'      => 14,
            'userId'  => 6,
            'scopeId' => 5,
        ]], $routes);
    }

    public function testPostNameExists()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/backend/user', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status'   => 0,
            'name'     => 'Consumer',
            'email'    => 'test@localhost.com',
            'password' => 'fooo123!',
            'scopes'   => ['foo', 'bar'],
        ]));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "User name already exists"
}
JSON;

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPostEmailExists()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/backend/user', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status'   => 0,
            'name'     => 'test',
            'email'    => 'consumer@localhost.com',
            'password' => 'fooo123!',
            'scopes'   => ['foo', 'bar'],
        ]));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "User email already exists"
}
JSON;

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/user', 'PUT', array(
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
        $response = $this->sendRequest('/backend/user', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

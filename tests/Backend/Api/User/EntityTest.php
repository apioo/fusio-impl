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

namespace Fusio\Impl\Tests\Backend\Api\User;

use Fusio\Impl\Table\User;
use Fusio\Impl\Tests\Fixture;
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
        $response = $this->sendRequest('/doc/*/backend/user/2', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/user\/$user_id<[0-9]+>",
    "version": "*",
    "status": 1,
    "description": null,
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
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
                        "pattern": "^[a-zA-Z0-9\\-\\_]{3,64}$"
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
                        "pattern": "^[a-zA-Z0-9\\-\\_\\.]{3,32}$"
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
                "$ref": "#\/definitions\/User"
            },
            "PUT-request": {
                "$ref": "#\/definitions\/User"
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
            "href": "\/export\/openapi\/*\/backend\/user\/$user_id<[0-9]+>"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/user\/$user_id<[0-9]+>"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/user\/$user_id<[0-9]+>"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/user/2', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = preg_replace('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/m', '[datetime]', $body);

        $expect = <<<'JSON'
{
    "id": 2,
    "provider": 1,
    "status": 0,
    "name": "Consumer",
    "email": "consumer@localhost.com",
    "date": "[datetime]",
    "scopes": [
        "consumer",
        "authorization",
        "foo",
        "bar"
    ],
    "apps": [
        {
            "id": 5,
            "status": 3,
            "name": "Deactivated",
            "url": "http:\/\/google.com",
            "appKey": "f46af464-f7eb-4d04-8661-13063a30826b",
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 2,
            "name": "Pending",
            "url": "http:\/\/google.com",
            "appKey": "7c14809c-544b-43bd-9002-23e1c2de6067",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "status": 1,
            "name": "Foo-App",
            "url": "http:\/\/google.com",
            "appKey": "5347307d-d801-4075-9aaa-a21a29a448c5",
            "date": "[datetime]"
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

        $response = $this->sendRequest('/backend/user/10', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Could not find user"
}
JSON;

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/user/3', 'POST', array(
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
        $response = $this->sendRequest('/backend/user/4', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status' => 1,
            'name'   => 'bar',
            'email'  => 'bar@bar.com',
            'scopes' => ['bar'],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "User successful updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'name', 'email')
            ->from('fusio_user')
            ->where('id = 4')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(4, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('bar', $row['name']);
        $this->assertEquals('bar@bar.com', $row['email']);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('userId', 'scopeId')
            ->from('fusio_user_scope')
            ->where('userId = :userId')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $scopes = Environment::getService('connection')->fetchAll($sql, ['userId' => 4]);

        $this->assertEquals([[
            'userId'  => 4,
            'scopeId' => 5,
        ]], $scopes);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/user/4', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "User successful deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_user')
            ->where('id = 4')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(4, $row['id']);
        $this->assertEquals(User::STATUS_DELETED, $row['status']);
    }
}

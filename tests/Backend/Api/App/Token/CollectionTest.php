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

namespace Fusio\Impl\Tests\Backend\Api\App\Token;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

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
        $response = $this->sendRequest('/doc/*/backend/app/token', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/app\/token",
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
                    "from": {
                        "type": "string",
                        "format": "date-time"
                    },
                    "to": {
                        "type": "string",
                        "format": "date-time"
                    },
                    "appId": {
                        "type": "integer"
                    },
                    "userId": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "scope": {
                        "type": "string"
                    },
                    "ip": {
                        "type": "string"
                    },
                    "search": {
                        "type": "string"
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
                            "$ref": "#\/definitions\/Token"
                        }
                    }
                }
            },
            "GET-200-response": {
                "$ref": "#\/definitions\/Collection"
            }
        }
    },
    "methods": {
        "GET": {
            "queryParameters": "#\/definitions\/GET-query",
            "responses": {
                "200": "#\/definitions\/GET-200-response"
            }
        }
    },
    "links": [
        {
            "rel": "openapi",
            "href": "\/export\/openapi\/*\/backend\/app\/token"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/app\/token"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/app\/token"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/app/token?from=2015-06-25T00:00:00&to=2015-06-25T23:59:59', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $expect = <<<JSON
{
    "totalResults": 5,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 5,
            "appId": 2,
            "userId": 2,
            "status": 1,
            "scope": [
                "consumer"
            ],
            "ip": "127.0.0.1",
            "date": "2015-06-25T22:49:09Z"
        },
        {
            "id": 4,
            "appId": 1,
            "userId": 4,
            "status": 1,
            "scope": [
                "backend"
            ],
            "ip": "127.0.0.1",
            "date": "2015-06-25T22:49:09Z"
        },
        {
            "id": 3,
            "appId": 3,
            "userId": 2,
            "status": 1,
            "scope": [
                "bar"
            ],
            "ip": "127.0.0.1",
            "date": "2015-06-25T22:49:09Z"
        },
        {
            "id": 2,
            "appId": 2,
            "userId": 1,
            "status": 1,
            "scope": [
                "consumer",
                "authorization"
            ],
            "ip": "127.0.0.1",
            "date": "2015-06-25T22:49:09Z"
        },
        {
            "id": 1,
            "appId": 1,
            "userId": 1,
            "status": 1,
            "scope": [
                "backend",
                "authorization"
            ],
            "ip": "127.0.0.1",
            "date": "2015-06-25T22:49:09Z"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/app/token?count=80&from=2015-06-25T00:00:00&to=2015-06-25T23:59:59', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $expect = <<<JSON
{
    "totalResults": 5,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 5,
            "appId": 2,
            "userId": 2,
            "status": 1,
            "scope": [
                "consumer"
            ],
            "ip": "127.0.0.1",
            "date": "2015-06-25T22:49:09Z"
        },
        {
            "id": 4,
            "appId": 1,
            "userId": 4,
            "status": 1,
            "scope": [
                "backend"
            ],
            "ip": "127.0.0.1",
            "date": "2015-06-25T22:49:09Z"
        },
        {
            "id": 3,
            "appId": 3,
            "userId": 2,
            "status": 1,
            "scope": [
                "bar"
            ],
            "ip": "127.0.0.1",
            "date": "2015-06-25T22:49:09Z"
        },
        {
            "id": 2,
            "appId": 2,
            "userId": 1,
            "status": 1,
            "scope": [
                "consumer",
                "authorization"
            ],
            "ip": "127.0.0.1",
            "date": "2015-06-25T22:49:09Z"
        },
        {
            "id": 1,
            "appId": 1,
            "userId": 1,
            "status": 1,
            "scope": [
                "backend",
                "authorization"
            ],
            "ip": "127.0.0.1",
            "date": "2015-06-25T22:49:09Z"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/app/token', 'POST', array(
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
        $response = $this->sendRequest('/backend/app/token', 'PUT', array(
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
        $response = $this->sendRequest('/backend/app/token', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

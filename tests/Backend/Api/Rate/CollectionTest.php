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

namespace Fusio\Impl\Tests\Backend\Api\Rate;

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
        $response = $this->sendRequest('/doc/*/backend/rate', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/rate",
    "version": "*",
    "status": 1,
    "description": null,
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "GET-query": {
                "type": "object",
                "title": "GetQuery",
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
            "Rate": {
                "type": "object",
                "title": "Rate",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "priority": {
                        "type": "integer",
                        "minimum": 0
                    },
                    "name": {
                        "type": "string",
                        "pattern": "^[a-zA-Z0-9\\-\\_]{3,64}$"
                    },
                    "rateLimit": {
                        "type": "integer",
                        "minimum": 0
                    },
                    "timespan": {
                        "type": "string",
                        "format": "duration"
                    },
                    "allocation": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Rate_Allocation"
                        }
                    }
                },
                "required": [
                    "name",
                    "rateLimit",
                    "timespan"
                ]
            },
            "Rate_Allocation": {
                "type": "object",
                "title": "Rate Allocation",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "routeId": {
                        "type": "integer"
                    },
                    "appId": {
                        "type": "integer"
                    },
                    "authenticated": {
                        "type": "boolean"
                    },
                    "parameters": {
                        "type": "string"
                    }
                }
            },
            "Rate_Collection": {
                "type": "object",
                "title": "Rate Collection",
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
                            "$ref": "#\/definitions\/Rate"
                        }
                    }
                }
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
                "$ref": "#\/definitions\/Rate_Collection"
            },
            "POST-request": {
                "$ref": "#\/definitions\/Rate"
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
            "href": "\/export\/openapi\/*\/backend\/rate"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/rate"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/rate"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/rate', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 4,
            "status": 1,
            "priority": 10,
            "name": "gold",
            "rateLimit": 16,
            "timespan": "P1M"
        },
        {
            "id": 3,
            "status": 1,
            "priority": 5,
            "name": "silver",
            "rateLimit": 8,
            "timespan": "P1M"
        },
        {
            "id": 2,
            "status": 1,
            "priority": 4,
            "name": "Default-Anonymous",
            "rateLimit": 60,
            "timespan": "PT1H"
        },
        {
            "id": 1,
            "status": 1,
            "priority": 0,
            "name": "Default",
            "rateLimit": 720,
            "timespan": "PT1H"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/rate?search=gol', 'GET', array(
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
            "id": 4,
            "status": 1,
            "priority": 10,
            "name": "gold",
            "rateLimit": 16,
            "timespan": "P1M"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/rate?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 4,
            "status": 1,
            "priority": 10,
            "name": "gold",
            "rateLimit": 16,
            "timespan": "P1M"
        },
        {
            "id": 3,
            "status": 1,
            "priority": 5,
            "name": "silver",
            "rateLimit": 8,
            "timespan": "P1M"
        },
        {
            "id": 2,
            "status": 1,
            "priority": 4,
            "name": "Default-Anonymous",
            "rateLimit": 60,
            "timespan": "PT1H"
        },
        {
            "id": 1,
            "status": 1,
            "priority": 0,
            "name": "Default",
            "rateLimit": 720,
            "timespan": "PT1H"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/rate', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'priority'  => 2,
            'name'      => 'Premium',
            'rateLimit' => 20,
            'timespan'  => 'P2M',
            'allocation'  => [[
                'routeId' => 1,
                'authenticated' => true,
                'parameters' => 'premium=1',
            ]],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Rate successful created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'priority', 'name', 'rate_limit', 'timespan')
            ->from('fusio_rate')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(5, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals(2, $row['priority']);
        $this->assertEquals('Premium', $row['name']);
        $this->assertEquals(20, $row['rate_limit']);
        $this->assertEquals('P2M', $row['timespan']);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'rate_id', 'route_id', 'app_id', 'authenticated', 'parameters')
            ->from('fusio_rate_allocation')
            ->where('rate_id = :rate_id')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $result = Environment::getService('connection')->fetchAll($sql, ['rate_id' => $row['id']]);

        $this->assertEquals(5, $result[0]['id']);
        $this->assertEquals(5, $result[0]['rate_id']);
        $this->assertEquals(1, $result[0]['route_id']);
        $this->assertEquals(null, $result[0]['app_id']);
        $this->assertEquals(1, $result[0]['authenticated']);
        $this->assertEquals('premium=1', $result[0]['parameters']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/rate', 'PUT', array(
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
        $response = $this->sendRequest('/backend/rate', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

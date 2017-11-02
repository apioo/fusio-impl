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

namespace Fusio\Impl\Tests\Backend\Api\Action;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * ExecuteTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ExecuteTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/action/execute/3', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/action\/execute\/$action_id<[0-9]+>",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "Body": {
                "type": "object",
                "title": "body",
                "additionalProperties": true
            },
            "Request": {
                "type": "object",
                "title": "request",
                "properties": {
                    "method": {
                        "type": "string",
                        "pattern": "GET|POST|PUT|PATCH|DELETE"
                    },
                    "uriFragments": {
                        "type": "string"
                    },
                    "parameters": {
                        "type": "string"
                    },
                    "headers": {
                        "type": "string"
                    },
                    "body": {
                        "$ref": "#\/definitions\/Body"
                    }
                },
                "required": [
                    "method"
                ]
            },
            "Headers": {
                "type": "object",
                "title": "headers",
                "additionalProperties": {
                    "type": "string"
                }
            },
            "Response": {
                "type": "object",
                "title": "response",
                "properties": {
                    "statusCode": {
                        "type": "integer"
                    },
                    "headers": {
                        "$ref": "#\/definitions\/Headers"
                    },
                    "body": {
                        "$ref": "#\/definitions\/Body"
                    }
                }
            },
            "POST-request": {
                "$ref": "#\/definitions\/Request"
            },
            "POST-200-response": {
                "$ref": "#\/definitions\/Response"
            }
        }
    },
    "methods": {
        "POST": {
            "request": "#\/definitions\/POST-request",
            "responses": {
                "200": "#\/definitions\/POST-200-response"
            }
        }
    },
    "links": [
        {
            "rel": "openapi",
            "href": "\/export\/openapi\/*\/backend\/action\/execute\/$action_id<[0-9]+>"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/action\/execute\/$action_id<[0-9]+>"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/action\/execute\/$action_id<[0-9]+>"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/action/execute/3', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/action/execute/4', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'method'       => 'GET',
            'uriFragments' => 'news_id=10',
            'parameters'   => 'count=10',
            'headers'      => 'Content-Type=application/json',
            'body'         => new \stdClass(),
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "statusCode": 200,
    "headers": {},
    "body": {
        "method": "GET",
        "headers": {
            "content-type": [
                "application\/json"
            ]
        },
        "uri_fragments": {
            "news_id": "10"
        },
        "parameters": {
            "count": "10"
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/action/execute/3', 'PUT', array(
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
        $response = $this->sendRequest('/backend/action/execute/3', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

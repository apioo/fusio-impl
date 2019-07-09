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

namespace Fusio\Impl\Tests\Export\Api;

use Datto\JsonRpc\Client;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * JsonRpcTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class JsonRpcTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/export/rpc', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/export\/rpc",
    "version": "*",
    "status": 1,
    "description": null,
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "Export_Rpc_Request_Call": {
                "type": "object",
                "title": "Export Rpc Request Call",
                "properties": {
                    "jsonrpc": {
                        "type": "string"
                    },
                    "method": {
                        "type": "string"
                    },
                    "params": {
                        "title": "Export Rpc Request Params",
                        "description": "Method params"
                    },
                    "id": {
                        "type": "integer"
                    }
                }
            },
            "Export_Rpc_Request": {
                "title": "Export Rpc Request",
                "oneOf": [
                    {
                        "$ref": "#\/definitions\/Export_Rpc_Request_Call"
                    },
                    {
                        "type": "array",
                        "title": "Export Rpc Request Batch",
                        "items": {
                            "$ref": "#\/definitions\/Export_Rpc_Request_Call"
                        }
                    }
                ]
            },
            "Export_Rpc_Response_Return_Success": {
                "type": "object",
                "title": "Export Rpc Response Return Success",
                "properties": {
                    "jsonrpc": {
                        "type": "string"
                    },
                    "result": {
                        "title": "Export Rpc Response Result",
                        "description": "Method result"
                    },
                    "id": {
                        "type": "integer"
                    }
                }
            },
            "Export_Rpc_Response_Return_Error": {
                "type": "object",
                "title": "Export Rpc Response Return Error",
                "properties": {
                    "jsonrpc": {
                        "type": "string"
                    },
                    "error": {
                        "$ref": "#\/definitions\/Export_Rpc_Response_Error"
                    },
                    "id": {
                        "type": "integer"
                    }
                }
            },
            "Export_Rpc_Response_Error": {
                "type": "object",
                "title": "Export Rpc Response Error",
                "properties": {
                    "code": {
                        "type": "integer"
                    },
                    "message": {
                        "type": "string"
                    },
                    "data": {
                        "title": "Export Rpc Response Error Data",
                        "description": "Error data"
                    }
                }
            },
            "Export_Rpc_Response": {
                "title": "Export Rpc Response",
                "oneOf": [
                    {
                        "title": "Export Rpc Response Return",
                        "oneOf": [
                            {
                                "$ref": "#\/definitions\/Export_Rpc_Response_Return_Success"
                            },
                            {
                                "$ref": "#\/definitions\/Export_Rpc_Response_Return_Error"
                            }
                        ]
                    },
                    {
                        "type": "array",
                        "title": "Export Rpc Response Batch",
                        "items": {
                            "title": "Export Rpc Response Return",
                            "oneOf": [
                                {
                                    "$ref": "#\/definitions\/Export_Rpc_Response_Return_Success"
                                },
                                {
                                    "$ref": "#\/definitions\/Export_Rpc_Response_Return_Error"
                                }
                            ]
                        }
                    }
                ]
            },
            "POST-request": {
                "$ref": "#\/definitions\/Export_Rpc_Request"
            },
            "POST-200-response": {
                "$ref": "#\/definitions\/Export_Rpc_Response"
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
            "href": "\/export\/openapi\/*\/export\/rpc"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/export\/rpc"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/export\/rpc"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/export/rpc', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $client = new Client();
        $client->query(1, 'listFoo', []);
        $message = $client->encode();

        $response = $this->sendRequest('/export/rpc', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), $message);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "jsonrpc": "2.0",
    "id": 1,
    "result": {
        "totalResults": 2,
        "itemsPerPage": 16,
        "startIndex": 0,
        "entry": [
            {
                "id": 2,
                "title": "bar",
                "content": "foo",
                "date": "2015-02-27T19:59:15+00:00"
            },
            {
                "id": 1,
                "title": "foo",
                "content": "bar",
                "date": "2015-02-27T19:59:15+00:00"
            }
        ]
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/export/rpc', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/export/rpc', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

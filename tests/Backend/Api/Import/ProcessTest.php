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

namespace Fusio\Impl\Tests\Backend\Api\Import;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Http\Stream\StringStream;

/**
 * ProcessTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ProcessTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/import/process', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/import\/process",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
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
                    "config": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Version"
                        }
                    }
                }
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
                        "type": "string"
                    },
                    "request": {
                        "type": "string"
                    },
                    "response": {
                        "type": "string"
                    },
                    "responses": {
                        "$ref": "#\/definitions\/Responses"
                    },
                    "action": {
                        "type": "string"
                    }
                }
            },
            "Responses": {
                "type": "object",
                "title": "responses",
                "patternProperties": {
                    "^([0-9]{3})$": {
                        "type": "string"
                    }
                }
            },
            "Action": {
                "type": "object",
                "title": "action",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string",
                        "pattern": "[a-zA-Z0-9\\-\\_]{3,64}"
                    },
                    "class": {
                        "type": "string"
                    },
                    "engine": {
                        "type": "string"
                    },
                    "config": {
                        "$ref": "#\/definitions\/Config"
                    }
                }
            },
            "Config": {
                "type": "object",
                "title": "config",
                "additionalProperties": {
                    "oneOf": [
                        {
                            "type": "string"
                        },
                        {
                            "type": "number"
                        },
                        {
                            "type": "boolean"
                        },
                        {
                            "type": "null"
                        },
                        {
                            "type": "array",
                            "items": {
                                "oneOf": [
                                    {
                                        "type": "string"
                                    },
                                    {
                                        "type": "number"
                                    },
                                    {
                                        "type": "boolean"
                                    },
                                    {
                                        "type": "null"
                                    }
                                ]
                            },
                            "maxItems": 16
                        }
                    ]
                },
                "maxProperties": 16
            },
            "Schema": {
                "type": "object",
                "title": "schema",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string",
                        "pattern": "[a-zA-Z0-9\\-\\_]{3,64}"
                    },
                    "source": {
                        "$ref": "#\/definitions\/Source"
                    }
                }
            },
            "Source": {
                "type": "object",
                "title": "source",
                "additionalProperties": true
            },
            "Connection": {
                "type": "object",
                "title": "connection",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string",
                        "pattern": "[a-zA-Z0-9\\-\\_]{3,64}"
                    },
                    "class": {
                        "type": "string"
                    },
                    "config": {
                        "$ref": "#\/definitions\/Config"
                    }
                }
            },
            "Adapter": {
                "type": "object",
                "title": "adapter",
                "properties": {
                    "actionClass": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    },
                    "connectionClass": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    },
                    "routes": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Routes"
                        }
                    },
                    "action": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Action"
                        }
                    },
                    "schema": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Schema"
                        }
                    },
                    "connection": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Connection"
                        }
                    }
                }
            },
            "Response": {
                "type": "object",
                "title": "response",
                "properties": {
                    "success": {
                        "type": "boolean"
                    },
                    "message": {
                        "type": "string"
                    },
                    "result": {
                        "type": "array"
                    }
                }
            },
            "POST-request": {
                "$ref": "#\/definitions\/Adapter"
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
            "href": "\/export\/openapi\/*\/backend\/import\/process"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/import\/process"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/import\/process"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPost()
    {
        $data = $this->getData();
        $body = new StringStream($data);

        $response = $this->sendRequest('/backend/import/process', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf',
            'Content-Type'  => 'application/json',
        ), $body);

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "success": true,
    "message": "Import successful",
    "result": [
        "[CREATED] schema api-pet-petId-GET-response",
        "[CREATED] schema api-pet-POST-request",
        "[CREATED] schema api-pet-PUT-request",
        "[CREATED] routes \/api\/pet\/:petId",
        "[CREATED] routes \/api\/pet"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // @TODO check entries
    }

    protected function getData()
    {
        return <<<'JSON'
{
    "routes": [
        {
            "path": "\/api\/pet\/:petId",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "parameters": "Passthru",
                            "request": "Passthru",
                            "responses": {
                                "200": "api-pet-petId-GET-response"
                            },
                            "action": "Welcome"
                        }
                    }
                }
            ]
        },
        {
            "path": "\/api\/pet",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "POST": {
                            "active": true,
                            "public": true,
                            "parameters": "Passthru",
                            "request": "api-pet-POST-request",
                            "responses": {
                                "200": "Passthru"
                            },
                            "action": "Welcome"
                        },
                        "PUT": {
                            "active": true,
                            "public": true,
                            "parameters": "Passthru",
                            "request": "api-pet-PUT-request",
                            "responses": {
                                "200": "Passthru"
                            },
                            "action": "Welcome"
                        }
                    }
                }
            ]
        }
    ],
    "schema": [
        {
            "name": "api-pet-petId-GET-response",
            "source": {
                "type": "object",
                "title": "Pet",
                "properties": {
                    "id": {
                        "type": "integer",
                        "required": true,
                        "title": "id"
                    },
                    "name": {
                        "type": "string",
                        "required": true,
                        "title": "name"
                    }
                }
            }
        },
        {
            "name": "api-pet-POST-request",
            "source": {
                "type": "object",
                "title": "Pet",
                "properties": {
                    "id": {
                        "type": "integer",
                        "required": true,
                        "title": "id"
                    },
                    "name": {
                        "type": "string",
                        "required": true,
                        "title": "name"
                    }
                }
            }
        },
        {
            "name": "api-pet-PUT-request",
            "source": {
                "type": "object",
                "title": "Pet",
                "properties": {
                    "id": {
                        "type": "integer",
                        "required": true,
                        "title": "id"
                    },
                    "name": {
                        "type": "string",
                        "required": true,
                        "title": "name"
                    }
                }
            }
        }
    ]
}
JSON;
    }
}

<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\System\Api;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * SchemaTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SchemaTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/system/schema/Entry-Schema', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/schema.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/system/schema/Entry-Schema', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "schema": {
        "definitions": {
            "Entry": {
                "type": "object",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "title": {
                        "type": "string"
                    },
                    "content": {
                        "type": "string"
                    },
                    "date": {
                        "format": "date-time",
                        "type": "string"
                    }
                }
            }
        },
        "$ref": "Entry"
    },
    "form": {
        "title": {
            "ui:autofocus": true,
            "ui:emptyValue": ""
        },
        "content": {
            "ui:widget": "textarea"
        },
        "date": {
            "ui:widget": "alt-datetime"
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetWithId()
    {
        $response = $this->sendRequest('/system/schema/3', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "schema": {
        "definitions": {
            "App": {
                "type": "object",
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
                        "format": "date-time",
                        "type": "string"
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
                            "$ref": "App_Token"
                        }
                    }
                }
            },
            "App_Token": {
                "type": "object",
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
                        "format": "date-time",
                        "type": "string"
                    },
                    "date": {
                        "format": "date-time",
                        "type": "string"
                    }
                }
            },
            "User": {
                "type": "object",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "roleId": {
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
                    "points": {
                        "type": "integer"
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
                            "$ref": "App"
                        }
                    },
                    "attributes": {
                        "$ref": "User_Attributes"
                    },
                    "date": {
                        "format": "date-time",
                        "type": "string"
                    }
                }
            },
            "User_Attributes": {
                "type": "object",
                "additionalProperties": {
                    "type": "string"
                }
            }
        },
        "$ref": "User"
    },
    "form": null
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/system/schema/not_available', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Could not find schema"
}
JSON;

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/system/schema/Entry-Schema', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/system/schema/Entry-Schema', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/system/schema/Entry-Schema', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

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

namespace Fusio\Impl\Tests\Backend\Api\Audit;

use Fusio\Impl\Table;
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
        $response = $this->sendRequest('http://127.0.0.1/doc/*/backend/audit', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/audit",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "Audit": {
                "type": "object",
                "title": "audit",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "app": {
                        "$ref": "#\/definitions\/App"
                    },
                    "user": {
                        "$ref": "#\/definitions\/User"
                    },
                    "event": {
                        "type": "string"
                    },
                    "ip": {
                        "type": "string"
                    },
                    "date": {
                        "type": "string",
                        "format": "date-time"
                    }
                }
            },
            "App": {
                "type": "object",
                "title": "app",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "name": {
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
                        "type": "string"
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
                            "$ref": "#\/definitions\/Audit"
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
            "responses": {
                "200": "#\/definitions\/GET-200-response"
            }
        }
    },
    "links": [
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/audit"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/audit"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/audit', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = preg_replace('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/m', '[datetime]', $body);

        $expect = <<<JSON
{
    "totalResults": 10,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": "10",
            "app": {
                "id": "1",
                "status": "1",
                "name": "Backend"
            },
            "user": {
                "id": "1",
                "status": "1",
                "name": "Administrator"
            },
            "event": "app.remove_token",
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": "9",
            "app": {
                "id": "1",
                "status": "1",
                "name": "Backend"
            },
            "user": {
                "id": "1",
                "status": "1",
                "name": "Administrator"
            },
            "event": "app.delete",
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": "8",
            "app": {
                "id": "1",
                "status": "1",
                "name": "Backend"
            },
            "user": {
                "id": "1",
                "status": "1",
                "name": "Administrator"
            },
            "event": "app.update",
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": "7",
            "app": {
                "id": "1",
                "status": "1",
                "name": "Backend"
            },
            "user": {
                "id": "1",
                "status": "1",
                "name": "Administrator"
            },
            "event": "app.update",
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": "6",
            "app": {
                "id": "1",
                "status": "1",
                "name": "Backend"
            },
            "user": {
                "id": "1",
                "status": "1",
                "name": "Administrator"
            },
            "event": "app.create",
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": "5",
            "app": {
                "id": "1",
                "status": "1",
                "name": "Backend"
            },
            "user": {
                "id": "1",
                "status": "1",
                "name": "Administrator"
            },
            "event": "app.create",
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": "4",
            "app": {
                "id": "1",
                "status": "1",
                "name": "Backend"
            },
            "user": {
                "id": "1",
                "status": "1",
                "name": "Administrator"
            },
            "event": "action.delete",
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": "3",
            "app": {
                "id": "1",
                "status": "1",
                "name": "Backend"
            },
            "user": {
                "id": "1",
                "status": "1",
                "name": "Administrator"
            },
            "event": "action.update",
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": "2",
            "app": {
                "id": "1",
                "status": "1",
                "name": "Backend"
            },
            "user": {
                "id": "1",
                "status": "1",
                "name": "Administrator"
            },
            "event": "action.create",
            "ip": "127.0.0.1",
            "date": "[datetime]"
        },
        {
            "id": "1",
            "app": {
                "id": "1",
                "status": "1",
                "name": "Backend"
            },
            "user": {
                "id": "4",
                "status": "1",
                "name": "Developer"
            },
            "event": "user.change_password",
            "ip": "127.0.0.1",
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
        $response = $this->sendRequest('http://127.0.0.1/backend/audit', 'PUT', array(
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
        $response = $this->sendRequest('http://127.0.0.1/backend/audit', 'PUT', array(
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
        $response = $this->sendRequest('http://127.0.0.1/backend/audit', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

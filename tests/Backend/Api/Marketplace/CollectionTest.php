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

namespace Fusio\Impl\Tests\Backend\Api\Marketplace;

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
        $response = $this->sendRequest('/doc/*/backend/marketplace', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/marketplace",
    "version": "*",
    "status": 1,
    "description": null,
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "Marketplace_Collection_Apps": {
                "type": "object",
                "title": "Marketplace Collection Apps",
                "additionalProperties": {
                    "$ref": "#\/definitions\/Marketplace_App_Remote"
                }
            },
            "Marketplace_App_Remote": {
                "type": "object",
                "title": "Marketplace App Remote",
                "properties": {
                    "version": {
                        "type": "string"
                    },
                    "description": {
                        "type": "string"
                    },
                    "screenshot": {
                        "type": "string"
                    },
                    "website": {
                        "type": "string"
                    },
                    "downloadUrl": {
                        "type": "string"
                    },
                    "sha1Hash": {
                        "type": "string"
                    },
                    "local": {
                        "$ref": "#\/definitions\/Marketplace_App"
                    }
                }
            },
            "Marketplace_App": {
                "type": "object",
                "title": "Marketplace App",
                "properties": {
                    "version": {
                        "type": "string"
                    },
                    "description": {
                        "type": "string"
                    },
                    "screenshot": {
                        "type": "string"
                    },
                    "website": {
                        "type": "string"
                    },
                    "downloadUrl": {
                        "type": "string"
                    },
                    "sha1Hash": {
                        "type": "string"
                    }
                }
            },
            "Marketplace_Collection": {
                "type": "object",
                "title": "Marketplace Collection",
                "properties": {
                    "apps": {
                        "$ref": "#\/definitions\/Marketplace_Collection_Apps"
                    }
                }
            },
            "Marketplace_Install": {
                "type": "object",
                "title": "Marketplace Install",
                "properties": {
                    "name": {
                        "type": "string"
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
                "$ref": "#\/definitions\/Marketplace_Collection"
            },
            "POST-request": {
                "$ref": "#\/definitions\/Marketplace_Install"
            },
            "POST-201-response": {
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
            "href": "\/export\/openapi\/*\/backend\/marketplace"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/marketplace"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/marketplace"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/marketplace', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertArrayHasKey('fusio', $data['apps']);
        $this->assertArrayHasKey('developer', $data['apps']);
        $this->assertArrayHasKey('documentation', $data['apps']);
        $this->assertArrayHasKey('swagger-ui', $data['apps']);
        $this->assertArrayHasKey('vscode', $data['apps']);

        foreach ($data['apps'] as $app) {
            $this->assertNotEmpty($app['version']);
            $this->assertSame(version_compare($app['version'], '0.0'), 1);
            $this->assertNotEmpty($app['description']);
            $this->assertNotEmpty($app['screenshot']);
            $this->assertNotEmpty($app['website']);
            $this->assertNotEmpty($app['downloadUrl']);
            $this->assertNotEmpty($app['sha1Hash']);

            // @TODO maybe check whether the download url actual exists
        }
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/marketplace', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name' => 'fusio',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "App fusio successful installed"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/marketplace', 'PUT', array(
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
        $response = $this->sendRequest('/backend/marketplace', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

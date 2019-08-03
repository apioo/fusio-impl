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

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * RoutesTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RoutesTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/export/routes', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/export\/routes",
    "version": "*",
    "status": 1,
    "description": null,
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "Export_Routes_Paths": {
                "type": "object",
                "title": "Export Routes Paths",
                "additionalProperties": {
                    "$ref": "#\/definitions\/Export_Routes_Methods"
                }
            },
            "Export_Routes_Methods": {
                "type": "object",
                "title": "Export Routes Methods",
                "additionalProperties": {
                    "type": "string"
                }
            },
            "Export_Routes": {
                "type": "object",
                "title": "Export Routes",
                "properties": {
                    "routes": {
                        "$ref": "#\/definitions\/Export_Routes_Paths"
                    }
                }
            },
            "GET-200-response": {
                "$ref": "#\/definitions\/Export_Routes"
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
            "rel": "openapi",
            "href": "\/export\/openapi\/*\/export\/routes"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/export\/routes"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/export\/routes"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/export/routes', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "routes": {
        "\/inspect\/:foo": {
            "GET": "Fusio\\Impl\\Tests\\Adapter\\Test\\InspectAction",
            "POST": "Fusio\\Impl\\Tests\\Adapter\\Test\\InspectAction",
            "PUT": "Fusio\\Impl\\Tests\\Adapter\\Test\\InspectAction",
            "PATCH": "Fusio\\Impl\\Tests\\Adapter\\Test\\InspectAction",
            "DELETE": "Fusio\\Impl\\Tests\\Adapter\\Test\\InspectAction"
        },
        "\/foo": {
            "GET": "Fusio\\Adapter\\Sql\\Action\\SqlTable",
            "POST": "Fusio\\Adapter\\Sql\\Action\\SqlTable"
        },
        "\/": {
            "GET": "Fusio\\Impl\\Action\\Welcome"
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/export/routes', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/export/routes', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/export/routes', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

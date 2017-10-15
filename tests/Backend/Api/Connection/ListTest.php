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

namespace Fusio\Impl\Tests\Backend\Api\Connection;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * ListTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ListTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/connection/list', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/connection\/list",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "Connection": {
                "type": "object",
                "title": "connection",
                "properties": {
                    "name": {
                        "type": "string"
                    },
                    "class": {
                        "type": "string"
                    }
                }
            },
            "Index": {
                "type": "object",
                "title": "index",
                "properties": {
                    "connections": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Connection"
                        }
                    }
                }
            },
            "GET-200-response": {
                "$ref": "#\/definitions\/Index"
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
            "href": "\/export\/openapi\/*\/backend\/connection\/list"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/connection\/list"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/connection\/list"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/connection/list', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "connections": [
        {
            "name": "HTTP",
            "class": "Fusio\\Adapter\\Http\\Connection\\Http"
        },
        {
            "name": "SQL",
            "class": "Fusio\\Adapter\\Sql\\Connection\\Sql"
        },
        {
            "name": "SQL (advanced)",
            "class": "Fusio\\Adapter\\Sql\\Connection\\SqlAdvanced"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

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

namespace Fusio\Impl\Tests\Backend\Api\Statistic;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * UsedPointsTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class UsedPointsTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/statistic/used_points', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/statistic\/used_points",
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
                    "from": {
                        "type": "string",
                        "format": "date-time"
                    },
                    "to": {
                        "type": "string",
                        "format": "date-time"
                    },
                    "routeId": {
                        "type": "integer"
                    },
                    "appId": {
                        "type": "integer"
                    },
                    "userId": {
                        "type": "integer"
                    },
                    "search": {
                        "type": "string"
                    }
                }
            },
            "Statistic_Chart": {
                "type": "object",
                "title": "Statistic Chart",
                "properties": {
                    "labels": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    },
                    "data": {
                        "type": "array",
                        "items": {
                            "type": "array",
                            "items": {
                                "type": "number"
                            }
                        }
                    },
                    "series": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    }
                }
            },
            "GET-200-response": {
                "$ref": "#\/definitions\/Statistic_Chart"
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
            "href": "\/export\/openapi\/*\/backend\/statistic\/used_points"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/statistic\/used_points"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/statistic\/used_points"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/statistic/used_points?from=2018-10-01T00:00:00&to=2018-10-31T23:59:59', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $expect = <<<JSON
{
    "labels": [
        "2018-10-01",
        "2018-10-02",
        "2018-10-03",
        "2018-10-04",
        "2018-10-05",
        "2018-10-06",
        "2018-10-07",
        "2018-10-08",
        "2018-10-09",
        "2018-10-10",
        "2018-10-11",
        "2018-10-12",
        "2018-10-13",
        "2018-10-14",
        "2018-10-15",
        "2018-10-16",
        "2018-10-17",
        "2018-10-18",
        "2018-10-19",
        "2018-10-20",
        "2018-10-21",
        "2018-10-22",
        "2018-10-23",
        "2018-10-24",
        "2018-10-25",
        "2018-10-26",
        "2018-10-27",
        "2018-10-28",
        "2018-10-29",
        "2018-10-30",
        "2018-10-31"
    ],
    "data": [
        [
            0,
            0,
            0,
            0,
            1,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0,
            0
        ]
    ],
    "series": [
        "Points"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

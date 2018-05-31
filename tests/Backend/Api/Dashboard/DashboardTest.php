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

namespace Fusio\Impl\Tests\Backend\Api\Dashboard;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * DashboardTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DashboardTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/dashboard', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/dashboard",
    "version": "*",
    "status": 1,
    "description": null,
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
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
            "Dashboard_Apps": {
                "type": "object",
                "title": "Dashboard Apps",
                "properties": {
                    "entry": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Dashboard_App"
                        }
                    }
                }
            },
            "Dashboard_App": {
                "type": "object",
                "title": "Dashboard App",
                "properties": {
                    "name": {
                        "type": "string"
                    },
                    "date": {
                        "type": "string",
                        "format": "date-time"
                    }
                }
            },
            "Dashboard_Requests": {
                "type": "object",
                "title": "Dashboard Requests",
                "properties": {
                    "entry": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Dashboard_Request"
                        }
                    }
                }
            },
            "Dashboard_Request": {
                "type": "object",
                "title": "Dashboard Request",
                "properties": {
                    "path": {
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
            "Dashboard": {
                "type": "object",
                "title": "Dashboard",
                "properties": {
                    "incomingRequests": {
                        "$ref": "#\/definitions\/Statistic_Chart"
                    },
                    "mostUsedRoutes": {
                        "$ref": "#\/definitions\/Statistic_Chart"
                    },
                    "timePerRoute": {
                        "$ref": "#\/definitions\/Statistic_Chart"
                    },
                    "latestApps": {
                        "$ref": "#\/definitions\/Dashboard_Apps"
                    },
                    "latestRequests": {
                        "$ref": "#\/definitions\/Dashboard_Requests"
                    },
                    "errorsPerRoute": {
                        "$ref": "#\/definitions\/Statistic_Chart"
                    }
                }
            },
            "GET-200-response": {
                "$ref": "#\/definitions\/Dashboard"
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
            "href": "\/export\/openapi\/*\/backend\/dashboard"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/dashboard"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/dashboard"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/dashboard?from=2015-06-01T00:00:00&to=2015-06-30T23:59:59', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $actual = preg_replace('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/m', '[datetime]', $actual);
        $expect = <<<JSON
{
    "incomingRequests": {
        "labels": [
            "2015-06-01",
            "2015-06-02",
            "2015-06-03",
            "2015-06-04",
            "2015-06-05",
            "2015-06-06",
            "2015-06-07",
            "2015-06-08",
            "2015-06-09",
            "2015-06-10",
            "2015-06-11",
            "2015-06-12",
            "2015-06-13",
            "2015-06-14",
            "2015-06-15",
            "2015-06-16",
            "2015-06-17",
            "2015-06-18",
            "2015-06-19",
            "2015-06-20",
            "2015-06-21",
            "2015-06-22",
            "2015-06-23",
            "2015-06-24",
            "2015-06-25",
            "2015-06-26",
            "2015-06-27",
            "2015-06-28",
            "2015-06-29",
            "2015-06-30"
        ],
        "data": [
            [
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
                2,
                0,
                0,
                0,
                0,
                0
            ]
        ],
        "series": [
            "Requests"
        ]
    },
    "mostUsedRoutes": {
        "labels": [
            "2015-06-01",
            "2015-06-02",
            "2015-06-03",
            "2015-06-04",
            "2015-06-05",
            "2015-06-06",
            "2015-06-07",
            "2015-06-08",
            "2015-06-09",
            "2015-06-10",
            "2015-06-11",
            "2015-06-12",
            "2015-06-13",
            "2015-06-14",
            "2015-06-15",
            "2015-06-16",
            "2015-06-17",
            "2015-06-18",
            "2015-06-19",
            "2015-06-20",
            "2015-06-21",
            "2015-06-22",
            "2015-06-23",
            "2015-06-24",
            "2015-06-25",
            "2015-06-26",
            "2015-06-27",
            "2015-06-28",
            "2015-06-29",
            "2015-06-30"
        ],
        "data": [
            [
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
                2,
                0,
                0,
                0,
                0,
                0
            ]
        ],
        "series": [
            "\/backend\/action"
        ]
    },
    "timePerRoute": {
        "labels": [
            "2015-06-01",
            "2015-06-02",
            "2015-06-03",
            "2015-06-04",
            "2015-06-05",
            "2015-06-06",
            "2015-06-07",
            "2015-06-08",
            "2015-06-09",
            "2015-06-10",
            "2015-06-11",
            "2015-06-12",
            "2015-06-13",
            "2015-06-14",
            "2015-06-15",
            "2015-06-16",
            "2015-06-17",
            "2015-06-18",
            "2015-06-19",
            "2015-06-20",
            "2015-06-21",
            "2015-06-22",
            "2015-06-23",
            "2015-06-24",
            "2015-06-25",
            "2015-06-26",
            "2015-06-27",
            "2015-06-28",
            "2015-06-29",
            "2015-06-30"
        ],
        "data": [
            [
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
                500,
                0,
                0,
                0,
                0,
                0
            ]
        ],
        "series": [
            "\/backend\/action (ms)"
        ]
    },
    "latestApps": {
        "entry": [
            {
                "name": "Backend",
                "date": "[datetime]"
            },
            {
                "name": "Consumer",
                "date": "[datetime]"
            },
            {
                "name": "Foo-App",
                "date": "[datetime]"
            },
            {
                "name": "Pending",
                "date": "[datetime]"
            },
            {
                "name": "Deactivated",
                "date": "[datetime]"
            }
        ]
    },
    "latestRequests": {
        "entry": [
            {
                "path": "\/bar",
                "ip": "127.0.0.1",
                "date": "[datetime]"
            },
            {
                "path": "\/bar",
                "ip": "127.0.0.1",
                "date": "[datetime]"
            }
        ]
    },
    "errorsPerRoute": {
        "labels": [
            "2015-06-01",
            "2015-06-02",
            "2015-06-03",
            "2015-06-04",
            "2015-06-05",
            "2015-06-06",
            "2015-06-07",
            "2015-06-08",
            "2015-06-09",
            "2015-06-10",
            "2015-06-11",
            "2015-06-12",
            "2015-06-13",
            "2015-06-14",
            "2015-06-15",
            "2015-06-16",
            "2015-06-17",
            "2015-06-18",
            "2015-06-19",
            "2015-06-20",
            "2015-06-21",
            "2015-06-22",
            "2015-06-23",
            "2015-06-24",
            "2015-06-25",
            "2015-06-26",
            "2015-06-27",
            "2015-06-28",
            "2015-06-29",
            "2015-06-30"
        ],
        "data": [
            [
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
                1,
                0,
                0,
                0,
                0,
                0
            ]
        ],
        "series": [
            "\/backend\/action"
        ]
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}

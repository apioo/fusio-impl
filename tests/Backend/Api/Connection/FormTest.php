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

namespace Fusio\Impl\Tests\Backend\Api\Connection;

use Fusio\Adapter\Sql\Connection\Sql;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * FormTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class FormTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/connection/form', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/connection\/form",
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
                    "class": {
                        "type": "string"
                    }
                }
            },
            "Input": {
                "type": "object",
                "title": "input",
                "properties": {
                    "element": {
                        "type": "string"
                    },
                    "name": {
                        "type": "string"
                    },
                    "title": {
                        "type": "string"
                    },
                    "help": {
                        "type": "string"
                    },
                    "type": {
                        "type": "string"
                    }
                }
            },
            "Select": {
                "type": "object",
                "title": "select",
                "properties": {
                    "element": {
                        "type": "string"
                    },
                    "name": {
                        "type": "string"
                    },
                    "title": {
                        "type": "string"
                    },
                    "help": {
                        "type": "string"
                    },
                    "options": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Option"
                        }
                    }
                }
            },
            "Option": {
                "type": "object",
                "title": "option",
                "properties": {
                    "key": {
                        "type": "string"
                    },
                    "value": {
                        "type": "string"
                    }
                }
            },
            "Tag": {
                "type": "object",
                "title": "tag",
                "properties": {
                    "element": {
                        "type": "string"
                    },
                    "name": {
                        "type": "string"
                    },
                    "title": {
                        "type": "string"
                    },
                    "help": {
                        "type": "string"
                    }
                }
            },
            "Textarea": {
                "type": "object",
                "title": "textarea",
                "properties": {
                    "element": {
                        "type": "string"
                    },
                    "name": {
                        "type": "string"
                    },
                    "title": {
                        "type": "string"
                    },
                    "help": {
                        "type": "string"
                    },
                    "mode": {
                        "type": "string"
                    }
                }
            },
            "Container": {
                "type": "object",
                "title": "container",
                "properties": {
                    "element": {
                        "type": "array",
                        "items": {
                            "oneOf": [
                                {
                                    "$ref": "#\/definitions\/Input"
                                },
                                {
                                    "$ref": "#\/definitions\/Select"
                                },
                                {
                                    "$ref": "#\/definitions\/Tag"
                                },
                                {
                                    "$ref": "#\/definitions\/Textarea"
                                }
                            ]
                        }
                    }
                }
            },
            "GET-200-response": {
                "$ref": "#\/definitions\/Container"
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
            "href": "\/export\/openapi\/*\/backend\/connection\/form"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/connection\/form"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/connection\/form"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/connection/form?class=' . urlencode(Sql::class), 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "element": [
        {
            "element": "http:\/\/fusio-project.org\/ns\/2015\/form\/select",
            "options": [
                {
                    "key": "pdo_mysql",
                    "value": "MySQL"
                },
                {
                    "key": "pdo_pgsql",
                    "value": "PostgreSQL"
                },
                {
                    "key": "sqlsrv",
                    "value": "Microsoft SQL Server"
                },
                {
                    "key": "oci8",
                    "value": "Oracle Database"
                },
                {
                    "key": "sqlanywhere",
                    "value": "SAP Sybase SQL Anywhere"
                }
            ],
            "name": "type",
            "title": "Type",
            "help": "The driver which is used to connect to the database"
        },
        {
            "element": "http:\/\/fusio-project.org\/ns\/2015\/form\/input",
            "type": "text",
            "name": "host",
            "title": "Host",
            "help": "The IP or hostname of the database server"
        },
        {
            "element": "http:\/\/fusio-project.org\/ns\/2015\/form\/input",
            "type": "text",
            "name": "username",
            "title": "Username",
            "help": "The name of the database user"
        },
        {
            "element": "http:\/\/fusio-project.org\/ns\/2015\/form\/input",
            "type": "password",
            "name": "password",
            "title": "Password",
            "help": "The password of the database user"
        },
        {
            "element": "http:\/\/fusio-project.org\/ns\/2015\/form\/input",
            "type": "text",
            "name": "database",
            "title": "Database",
            "help": "The name of the database which is used upon connection"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

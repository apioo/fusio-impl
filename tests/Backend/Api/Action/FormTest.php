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

namespace Fusio\Impl\Tests\Backend\Api\Action;

use Fusio\Adapter\Util\Action\UtilStaticResponse;
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
        $response = $this->sendRequest('http://127.0.0.1/doc/*/backend/action/form', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/action\/form",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "GET-query": {
                "type": "object",
                "title": "query",
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
            "href": "\/export\/openapi\/*\/backend\/action\/form"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/action\/form"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/action\/form"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/action/form?class=' . urlencode(UtilStaticResponse::class), 'GET', array(
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
                    "key": 100,
                    "value": "Continue"
                },
                {
                    "key": 101,
                    "value": "Switching Protocols"
                },
                {
                    "key": 102,
                    "value": "Processing"
                },
                {
                    "key": 200,
                    "value": "OK"
                },
                {
                    "key": 201,
                    "value": "Created"
                },
                {
                    "key": 202,
                    "value": "Accepted"
                },
                {
                    "key": 203,
                    "value": "Non-Authoritative Information"
                },
                {
                    "key": 204,
                    "value": "No Content"
                },
                {
                    "key": 205,
                    "value": "Reset Content"
                },
                {
                    "key": 206,
                    "value": "Partial Content"
                },
                {
                    "key": 207,
                    "value": "Multi-Status"
                },
                {
                    "key": 208,
                    "value": "Already Reported"
                },
                {
                    "key": 226,
                    "value": "IM Used"
                },
                {
                    "key": 300,
                    "value": "Multiple Choices"
                },
                {
                    "key": 301,
                    "value": "Moved Permanently"
                },
                {
                    "key": 302,
                    "value": "Found"
                },
                {
                    "key": 303,
                    "value": "See Other"
                },
                {
                    "key": 304,
                    "value": "Not Modified"
                },
                {
                    "key": 305,
                    "value": "Use Proxy"
                },
                {
                    "key": 306,
                    "value": "Reserved"
                },
                {
                    "key": 307,
                    "value": "Temporary Redirect"
                },
                {
                    "key": 308,
                    "value": "Permanent Redirect"
                },
                {
                    "key": 400,
                    "value": "Bad Request"
                },
                {
                    "key": 401,
                    "value": "Unauthorized"
                },
                {
                    "key": 402,
                    "value": "Payment Required"
                },
                {
                    "key": 403,
                    "value": "Forbidden"
                },
                {
                    "key": 404,
                    "value": "Not Found"
                },
                {
                    "key": 405,
                    "value": "Method Not Allowed"
                },
                {
                    "key": 406,
                    "value": "Not Acceptable"
                },
                {
                    "key": 407,
                    "value": "Proxy Authentication Required"
                },
                {
                    "key": 408,
                    "value": "Request Timeout"
                },
                {
                    "key": 409,
                    "value": "Conflict"
                },
                {
                    "key": 410,
                    "value": "Gone"
                },
                {
                    "key": 411,
                    "value": "Length Required"
                },
                {
                    "key": 412,
                    "value": "Precondition Failed"
                },
                {
                    "key": 413,
                    "value": "Request Entity Too Large"
                },
                {
                    "key": 414,
                    "value": "Request-URI Too Long"
                },
                {
                    "key": 415,
                    "value": "Unsupported Media Type"
                },
                {
                    "key": 416,
                    "value": "Requested Range Not Satisfiable"
                },
                {
                    "key": 417,
                    "value": "Expectation Failed"
                },
                {
                    "key": 418,
                    "value": "I'm a teapot"
                },
                {
                    "key": 422,
                    "value": "Unprocessable Entity"
                },
                {
                    "key": 423,
                    "value": "Locked"
                },
                {
                    "key": 424,
                    "value": "Failed Dependency"
                },
                {
                    "key": 425,
                    "value": "Reserved for WebDAV advanced collections expired proposal"
                },
                {
                    "key": 426,
                    "value": "Upgrade Required"
                },
                {
                    "key": 428,
                    "value": "Precondition Required"
                },
                {
                    "key": 429,
                    "value": "Too Many Requests"
                },
                {
                    "key": 431,
                    "value": "Request Header Fields Too Large"
                },
                {
                    "key": 500,
                    "value": "Internal Server Error"
                },
                {
                    "key": 501,
                    "value": "Not Implemented"
                },
                {
                    "key": 502,
                    "value": "Bad Gateway"
                },
                {
                    "key": 503,
                    "value": "Service Unavailable"
                },
                {
                    "key": 504,
                    "value": "Gateway Timeout"
                },
                {
                    "key": 505,
                    "value": "HTTP Version Not Supported"
                },
                {
                    "key": 506,
                    "value": "Variant Also Negotiates (Experimental)"
                },
                {
                    "key": 507,
                    "value": "Insufficient Storage"
                },
                {
                    "key": 508,
                    "value": "Loop Detected"
                },
                {
                    "key": 510,
                    "value": "Not Extended"
                },
                {
                    "key": 511,
                    "value": "Network Authentication Required"
                }
            ],
            "name": "statusCode",
            "title": "Status-Code",
            "help": "The returned status code"
        },
        {
            "element": "http:\/\/fusio-project.org\/ns\/2015\/form\/textarea",
            "mode": "json",
            "name": "response",
            "title": "Response",
            "help": "The response in JSON format"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

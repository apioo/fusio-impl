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

namespace Fusio\Impl\Tests\Backend\Api\Import;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * RamlTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RamlTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    /**
     * @dataProvider providerSpecs
     */
    public function testPost($spec, $expect)
    {
        $body     = json_encode(['schema' => $spec]);
        $response = $this->sendRequest('http://127.0.0.1/backend/import/raml', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf',
            'Content-Type'  => 'application/json',
        ), $body);

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function providerSpecs()
    {
        return [
            [$this->getCase01(), $this->getCase01Expect()],
            [$this->getCase02(), $this->getCase02Expect()],
            [$this->getCase03(), $this->getCase03Expect()],
        ];
    }

    protected function getCase01()
    {
        return <<<'RAML'
#%RAML 1.0
title: Hello world
/helloworld:
  get:
    responses:
      200:
        body:
          application/json:
            type: |
              {
                "title": "Hello world Response",
                "type": "object",
                "properties": {
                  "message": {
                    "type": "string"
                  }
                }
              }
            example: |
              {
                "message": "Hello world"
              }
RAML;
    }

    protected function getCase01Expect()
    {
        return <<<'JSON'
{
    "routes": [
        {
            "path": "\/helloworld",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "responses": {
                                "200": "helloworld-GET-200-response"
                            },
                            "action": "helloworld-GET"
                        }
                    }
                }
            ]
        }
    ],
    "action": [
        {
            "name": "helloworld-GET",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "statusCode": "200",
                "response": "{\"message\":\"Test implementation\"}"
            }
        }
    ],
    "schema": [
        {
            "name": "helloworld-GET-200-response",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "type": "object",
                "title": "Hello world Response",
                "properties": {
                    "message": {
                        "type": "string"
                    }
                }
            }
        }
    ]
}
JSON;
    }

    protected function getCase02()
    {
        return <<<'RAML'
#%RAML 1.0
title: Using XML and JSON Schema

schemas:
  PersonInline: |
    {
      "title": "Person Schema",
      "type": "object",
      "properties": {
        "firstName": {
          "type": "string"
        },
        "lastName": {
          "type": "string"
        },
        "age": {
          "description": "Age in years",
          "type": "integer",
          "minimum": 0
        }
      },
      "required": ["firstName", "lastName"]
    }

/person:
  get:
    responses:
      200:
        body:
          application/json:
            schema: PersonInline
  post:
    body:
      application/json:
        schema: |
          {
            "title": "Body Declaration Schema",
            "type": "object",
            "properties": {
              "firstName": {
                "type": "string"
              },
              "lastName": {
                "type": "string"
              }
            }
          }
RAML;
    }

    protected function getCase02Expect()
    {
        return <<<'JSON'
{
    "routes": [
        {
            "path": "\/person",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "responses": {
                                "200": "person-GET-200-response"
                            },
                            "action": "person-GET"
                        },
                        "POST": {
                            "active": true,
                            "public": true,
                            "request": "person-POST-request",
                            "action": "person-POST"
                        }
                    }
                }
            ]
        }
    ],
    "action": [
        {
            "name": "person-GET",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "statusCode": "200",
                "response": "{\"message\":\"Test implementation\"}"
            }
        },
        {
            "name": "person-POST",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "statusCode": "",
                "response": "{\"message\":\"Test implementation\"}"
            }
        }
    ],
    "schema": [
        {
            "name": "person-GET-200-response",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "type": "object",
                "title": "Person Schema",
                "properties": {
                    "firstName": {
                        "type": "string"
                    },
                    "lastName": {
                        "type": "string"
                    },
                    "age": {
                        "type": "integer",
                        "description": "Age in years",
                        "minimum": 0
                    }
                },
                "required": [
                    "firstName",
                    "lastName"
                ]
            }
        },
        {
            "name": "person-POST-request",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "type": "object",
                "title": "Body Declaration Schema",
                "properties": {
                    "firstName": {
                        "type": "string"
                    },
                    "lastName": {
                        "type": "string"
                    }
                }
            }
        }
    ]
}
JSON;
    }

    protected function getCase03()
    {
        return <<<'RAML'
#%RAML 0.8
---
title: GitHub API
version: v3
baseUri: https://api.github.com/
# Rate limit
/rate_limit:
  type: collection
  get:
    description: |
      Get your current rate limit status
      Note: Accessing this endpoint does not count against your rate limit.
    responses:
      200:
        body:
          application/json:
            schema: |
              {
                  "$schema": "http://json-schema.org/draft-03/schema",
                  "type": "object",
                  "properties": {
                      "rate": {
                          "title": "rate",
                          "properties": {
                              "limit": {
                                  "type": "integer"
                              },
                              "remaining": {
                                  "type": "integer"
                              },
                              "reset": {
                                  "type": "integer"
                              }
                          }
                      }
                  }
              }
            example: |
              {
                "rate": {
                  "limit": 5000,
                  "remaining": 4999,
                  "reset": 1372700873
                }
              }
RAML;
    }

    protected function getCase03Expect()
    {
        return <<<'JSON'
{
    "routes": [
        {
            "path": "\/rate_limit",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "responses": {
                                "200": "rate_limit-GET-200-response"
                            },
                            "action": "rate_limit-GET"
                        }
                    }
                }
            ]
        }
    ],
    "action": [
        {
            "name": "rate_limit-GET",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "statusCode": "200",
                "response": "{\"message\":\"Test implementation\"}"
            }
        }
    ],
    "schema": [
        {
            "name": "rate_limit-GET-200-response",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "definitions": {
                    "Rate": {
                        "title": "rate",
                        "properties": {
                            "limit": {
                                "type": "integer"
                            },
                            "remaining": {
                                "type": "integer"
                            },
                            "reset": {
                                "type": "integer"
                            }
                        }
                    }
                },
                "type": "object",
                "properties": {
                    "rate": {
                        "$ref": "#\/definitions\/Rate"
                    }
                }
            }
        }
    ]
}
JSON;
    }
}

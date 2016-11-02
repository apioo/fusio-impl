<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use PSX\Http\Stream\StringStream;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * SwaggerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SwaggerTest extends ControllerDbTestCase
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
        $body = new StringStream(json_encode(['schema' => $spec]));

        $response = $this->sendRequest('http://127.0.0.1/backend/import/swagger', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf',
            'Content-Type'  => 'application/json',
        ), $body);

        $body = (string) $response->getBody();

        $this->assertEquals(null, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function providerSpecs()
    {
        return [
            [$this->getCase01(), $this->getCase01Expect()],
            [$this->getCase02(), $this->getCase02Expect()],
        ];
    }

    protected function getCase01()
    {
        return <<<'JSON'
{
  "swagger": "2.0",
  "info": {
    "version": "1.0.0",
    "title": "Swagger Petstore",
    "description": "A sample API that uses a petstore as an example to demonstrate features in the swagger-2.0 specification",
    "termsOfService": "http://swagger.io/terms/",
    "contact": {
      "name": "Swagger API Team"
    },
    "license": {
      "name": "MIT"
    }
  },
  "host": "petstore.swagger.io",
  "basePath": "/api",
  "schemes": [
    "http"
  ],
  "consumes": [
    "application/json"
  ],
  "produces": [
    "application/json"
  ],
  "paths": {
    "/pets": {
      "get": {
        "description": "Returns all pets from the system that the user has access to",
        "operationId": "findPets",
        "produces": [
          "application/json",
          "application/xml",
          "text/xml",
          "text/html"
        ],
        "parameters": [
          {
            "name": "tags",
            "in": "query",
            "description": "tags to filter by",
            "required": false,
            "type": "array",
            "items": {
              "type": "string"
            },
            "collectionFormat": "csv"
          },
          {
            "name": "limit",
            "in": "query",
            "description": "maximum number of results to return",
            "required": false,
            "type": "integer",
            "format": "int32"
          }
        ],
        "responses": {
          "200": {
            "description": "pet response",
            "schema": {
              "type": "array",
              "items": {
                "$ref": "#/definitions/Pet"
              }
            }
          },
          "default": {
            "description": "unexpected error",
            "schema": {
              "$ref": "#/definitions/ErrorModel"
            }
          }
        }
      },
      "post": {
        "description": "Creates a new pet in the store.  Duplicates are allowed",
        "operationId": "addPet",
        "produces": [
          "application/json"
        ],
        "parameters": [
          {
            "name": "pet",
            "in": "body",
            "description": "Pet to add to the store",
            "required": true,
            "schema": {
              "$ref": "#/definitions/NewPet"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "pet response",
            "schema": {
              "$ref": "#/definitions/Pet"
            }
          },
          "default": {
            "description": "unexpected error",
            "schema": {
              "$ref": "#/definitions/ErrorModel"
            }
          }
        }
      }
    },
    "/pets/{id}": {
      "get": {
        "description": "Returns a user based on a single ID, if the user does not have access to the pet",
        "operationId": "findPetById",
        "produces": [
          "application/json",
          "application/xml",
          "text/xml",
          "text/html"
        ],
        "parameters": [
          {
            "name": "id",
            "in": "path",
            "description": "ID of pet to fetch",
            "required": true,
            "type": "integer",
            "format": "int64"
          }
        ],
        "responses": {
          "200": {
            "description": "pet response",
            "schema": {
              "$ref": "#/definitions/Pet"
            }
          },
          "default": {
            "description": "unexpected error",
            "schema": {
              "$ref": "#/definitions/ErrorModel"
            }
          }
        }
      },
      "delete": {
        "description": "deletes a single pet based on the ID supplied",
        "operationId": "deletePet",
        "parameters": [
          {
            "name": "id",
            "in": "path",
            "description": "ID of pet to delete",
            "required": true,
            "type": "integer",
            "format": "int64"
          }
        ],
        "responses": {
          "204": {
            "description": "pet deleted"
          },
          "default": {
            "description": "unexpected error",
            "schema": {
              "$ref": "#/definitions/ErrorModel"
            }
          }
        }
      }
    }
  },
  "definitions": {
    "Pet": {
      "type": "object",
      "allOf": [
        {
          "$ref": "#/definitions/NewPet"
        },
        {
          "required": [
            "id"
          ],
          "properties": {
            "id": {
              "type": "integer",
              "format": "int64"
            }
          }
        }
      ]
    },
    "NewPet": {
      "type": "object",
      "required": [
        "name"
      ],
      "properties": {
        "name": {
          "type": "string"
        },
        "tag": {
          "type": "string"
        }
      }
    },
    "ErrorModel": {
      "type": "object",
      "required": [
        "code",
        "message"
      ],
      "properties": {
        "code": {
          "type": "integer",
          "format": "int32"
        },
        "message": {
          "type": "string"
        }
      }
    }
  }
}
JSON;
    }

    protected function getCase01Expect()
    {
        return <<<'JSON'
{
    "routes": [
        {
            "path": "\/api\/pets",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "action": "Welcome",
                            "request": "Passthru",
                            "response": "findPets-GET-response"
                        },
                        "POST": {
                            "active": true,
                            "public": true,
                            "action": "Welcome",
                            "request": "addPet-POST-request",
                            "response": "addPet-POST-response"
                        }
                    }
                }
            ]
        },
        {
            "path": "\/api\/pets\/:id",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "action": "Welcome",
                            "request": "Passthru",
                            "response": "findPetById-GET-response"
                        },
                        "DELETE": {
                            "active": true,
                            "public": true,
                            "action": "Welcome",
                            "request": "Passthru",
                            "response": "Passthru"
                        }
                    }
                }
            ]
        }
    ],
    "schema": [
        {
            "name": "findPets-GET-response",
            "source": {
                "type": "array",
                "items": {
                    "type": "object",
                    "allOf": [
                        {
                            "type": "object",
                            "required": [
                                "name"
                            ],
                            "properties": {
                                "name": {
                                    "type": "string"
                                },
                                "tag": {
                                    "type": "string"
                                }
                            }
                        },
                        {
                            "required": [
                                "id"
                            ],
                            "properties": {
                                "id": {
                                    "type": "integer",
                                    "format": "int64"
                                }
                            }
                        }
                    ]
                }
            }
        },
        {
            "name": "addPet-POST-request",
            "source": {
                "type": "object",
                "required": [
                    "name"
                ],
                "properties": {
                    "name": {
                        "type": "string"
                    },
                    "tag": {
                        "type": "string"
                    }
                }
            }
        },
        {
            "name": "addPet-POST-response",
            "source": {
                "type": "object",
                "allOf": [
                    {
                        "type": "object",
                        "required": [
                            "name"
                        ],
                        "properties": {
                            "name": {
                                "type": "string"
                            },
                            "tag": {
                                "type": "string"
                            }
                        }
                    },
                    {
                        "required": [
                            "id"
                        ],
                        "properties": {
                            "id": {
                                "type": "integer",
                                "format": "int64"
                            }
                        }
                    }
                ]
            }
        },
        {
            "name": "findPetById-GET-response",
            "source": {
                "type": "object",
                "allOf": [
                    {
                        "type": "object",
                        "required": [
                            "name"
                        ],
                        "properties": {
                            "name": {
                                "type": "string"
                            },
                            "tag": {
                                "type": "string"
                            }
                        }
                    },
                    {
                        "required": [
                            "id"
                        ],
                        "properties": {
                            "id": {
                                "type": "integer",
                                "format": "int64"
                            }
                        }
                    }
                ]
            }
        }
    ]
}
JSON;
    }

    protected function getCase02()
    {
        return <<<'JSON'
{
  "swagger": "2.0",
  "info": {
    "title": "Simple API overview",
    "version": "v2"
  },
  "paths": {
    "/": {
      "get": {
        "operationId": "listVersionsv2",
        "summary": "List API versions",
        "produces": [
          "application/json"
        ],
        "responses": {
          "200": {
            "description": "200 300 response",
            "examples": {
              "application/json": "{\n    \"versions\": [\n        {\n            \"status\": \"CURRENT\",\n            \"updated\": \"2011-01-21T11:33:21Z\",\n            \"id\": \"v2.0\",\n            \"links\": [\n                {\n                    \"href\": \"http://127.0.0.1:8774/v2/\",\n                    \"rel\": \"self\"\n                }\n            ]\n        },\n        {\n            \"status\": \"EXPERIMENTAL\",\n            \"updated\": \"2013-07-23T11:33:21Z\",\n            \"id\": \"v3.0\",\n            \"links\": [\n                {\n                    \"href\": \"http://127.0.0.1:8774/v3/\",\n                    \"rel\": \"self\"\n                }\n            ]\n        }\n    ]\n}"
            }
          },
          "300": {
            "description": "200 300 response",
            "examples": {
              "application/json": "{\n    \"versions\": [\n        {\n            \"status\": \"CURRENT\",\n            \"updated\": \"2011-01-21T11:33:21Z\",\n            \"id\": \"v2.0\",\n            \"links\": [\n                {\n                    \"href\": \"http://127.0.0.1:8774/v2/\",\n                    \"rel\": \"self\"\n                }\n            ]\n        },\n        {\n            \"status\": \"EXPERIMENTAL\",\n            \"updated\": \"2013-07-23T11:33:21Z\",\n            \"id\": \"v3.0\",\n            \"links\": [\n                {\n                    \"href\": \"http://127.0.0.1:8774/v3/\",\n                    \"rel\": \"self\"\n                }\n            ]\n        }\n    ]\n}"
            }
          }
        }
      }
    },
    "/v2": {
      "get": {
        "operationId": "getVersionDetailsv2",
        "summary": "Show API version details",
        "produces": [
          "application/json"
        ],
        "responses": {
          "200": {
            "description": "200 203 response",
            "examples": {
              "application/json": "{\n    \"version\": {\n        \"status\": \"CURRENT\",\n        \"updated\": \"2011-01-21T11:33:21Z\",\n        \"media-types\": [\n            {\n                \"base\": \"application/xml\",\n                \"type\": \"application/vnd.openstack.compute+xml;version=2\"\n            },\n            {\n                \"base\": \"application/json\",\n                \"type\": \"application/vnd.openstack.compute+json;version=2\"\n            }\n        ],\n        \"id\": \"v2.0\",\n        \"links\": [\n            {\n                \"href\": \"http://127.0.0.1:8774/v2/\",\n                \"rel\": \"self\"\n            },\n            {\n                \"href\": \"http://docs.openstack.org/api/openstack-compute/2/os-compute-devguide-2.pdf\",\n                \"type\": \"application/pdf\",\n                \"rel\": \"describedby\"\n            },\n            {\n                \"href\": \"http://docs.openstack.org/api/openstack-compute/2/wadl/os-compute-2.wadl\",\n                \"type\": \"application/vnd.sun.wadl+xml\",\n                \"rel\": \"describedby\"\n            },\n            {\n              \"href\": \"http://docs.openstack.org/api/openstack-compute/2/wadl/os-compute-2.wadl\",\n              \"type\": \"application/vnd.sun.wadl+xml\",\n              \"rel\": \"describedby\"\n            }\n        ]\n    }\n}"
            }
          },
          "203": {
            "description": "200 203 response",
            "examples": {
              "application/json": "{\n    \"version\": {\n        \"status\": \"CURRENT\",\n        \"updated\": \"2011-01-21T11:33:21Z\",\n        \"media-types\": [\n            {\n                \"base\": \"application/xml\",\n                \"type\": \"application/vnd.openstack.compute+xml;version=2\"\n            },\n            {\n                \"base\": \"application/json\",\n                \"type\": \"application/vnd.openstack.compute+json;version=2\"\n            }\n        ],\n        \"id\": \"v2.0\",\n        \"links\": [\n            {\n                \"href\": \"http://23.253.228.211:8774/v2/\",\n                \"rel\": \"self\"\n            },\n            {\n                \"href\": \"http://docs.openstack.org/api/openstack-compute/2/os-compute-devguide-2.pdf\",\n                \"type\": \"application/pdf\",\n                \"rel\": \"describedby\"\n            },\n            {\n                \"href\": \"http://docs.openstack.org/api/openstack-compute/2/wadl/os-compute-2.wadl\",\n                \"type\": \"application/vnd.sun.wadl+xml\",\n                \"rel\": \"describedby\"\n            }\n        ]\n    }\n}"
            }
          }
        }
      }
    }
  },
  "consumes": [
    "application/json"
  ]
}
JSON;

    }

    protected function getCase02Expect()
    {
        return <<<'JSON'
{
    "routes": [
        {
            "path": "\/",
            "config": [
                {
                    "version": 2,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "action": "listVersionsv2",
                            "request": "Passthru",
                            "response": "Passthru"
                        }
                    }
                }
            ]
        },
        {
            "path": "\/v2",
            "config": [
                {
                    "version": 2,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "action": "getVersionDetailsv2",
                            "request": "Passthru",
                            "response": "Passthru"
                        }
                    }
                }
            ]
        }
    ],
    "action": [
        {
            "name": "listVersionsv2",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "config": {
                "statusCode": "200",
                "response": "{\n    \"versions\": [\n        {\n            \"status\": \"CURRENT\",\n            \"updated\": \"2011-01-21T11:33:21Z\",\n            \"id\": \"v2.0\",\n            \"links\": [\n                {\n                    \"href\": \"http:\/\/127.0.0.1:8774\/v2\/\",\n                    \"rel\": \"self\"\n                }\n            ]\n        },\n        {\n            \"status\": \"EXPERIMENTAL\",\n            \"updated\": \"2013-07-23T11:33:21Z\",\n            \"id\": \"v3.0\",\n            \"links\": [\n                {\n                    \"href\": \"http:\/\/127.0.0.1:8774\/v3\/\",\n                    \"rel\": \"self\"\n                }\n            ]\n        }\n    ]\n}"
            }
        },
        {
            "name": "getVersionDetailsv2",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "config": {
                "statusCode": "200",
                "response": "{\n    \"version\": {\n        \"status\": \"CURRENT\",\n        \"updated\": \"2011-01-21T11:33:21Z\",\n        \"media-types\": [\n            {\n                \"base\": \"application\/xml\",\n                \"type\": \"application\/vnd.openstack.compute+xml;version=2\"\n            },\n            {\n                \"base\": \"application\/json\",\n                \"type\": \"application\/vnd.openstack.compute+json;version=2\"\n            }\n        ],\n        \"id\": \"v2.0\",\n        \"links\": [\n            {\n                \"href\": \"http:\/\/127.0.0.1:8774\/v2\/\",\n                \"rel\": \"self\"\n            },\n            {\n                \"href\": \"http:\/\/docs.openstack.org\/api\/openstack-compute\/2\/os-compute-devguide-2.pdf\",\n                \"type\": \"application\/pdf\",\n                \"rel\": \"describedby\"\n            },\n            {\n                \"href\": \"http:\/\/docs.openstack.org\/api\/openstack-compute\/2\/wadl\/os-compute-2.wadl\",\n                \"type\": \"application\/vnd.sun.wadl+xml\",\n                \"rel\": \"describedby\"\n            },\n            {\n              \"href\": \"http:\/\/docs.openstack.org\/api\/openstack-compute\/2\/wadl\/os-compute-2.wadl\",\n              \"type\": \"application\/vnd.sun.wadl+xml\",\n              \"rel\": \"describedby\"\n            }\n        ]\n    }\n}"
            }
        }
    ]
}
JSON;
    }
}

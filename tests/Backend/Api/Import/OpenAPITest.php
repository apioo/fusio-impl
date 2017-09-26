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
use PSX\Http\Stream\StringStream;

/**
 * OpenAPITest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class OpenAPITest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/import/:format', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/backend\/import\/:format",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "Schema": {
                "type": "object",
                "title": "schema",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string",
                        "pattern": "[a-zA-Z0-9\\-\\_]{3,64}"
                    },
                    "source": {
                        "$ref": "#\/definitions\/Source"
                    }
                }
            },
            "Routes": {
                "type": "object",
                "title": "routes",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "path": {
                        "type": "string"
                    },
                    "config": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Version"
                        }
                    }
                }
            },
            "Version": {
                "type": "object",
                "title": "version",
                "properties": {
                    "version": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "scopes": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    },
                    "methods": {
                        "$ref": "#\/definitions\/Methods"
                    }
                }
            },
            "Methods": {
                "type": "object",
                "title": "methods",
                "patternProperties": {
                    "^(GET|POST|PUT|PATCH|DELETE)$": {
                        "$ref": "#\/definitions\/Method"
                    }
                }
            },
            "Method": {
                "type": "object",
                "title": "method",
                "properties": {
                    "method": {
                        "type": "string"
                    },
                    "version": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "active": {
                        "type": "boolean"
                    },
                    "public": {
                        "type": "boolean"
                    },
                    "parameters": {
                        "type": "string"
                    },
                    "request": {
                        "type": "string"
                    },
                    "response": {
                        "type": "string"
                    },
                    "responses": {
                        "$ref": "#\/definitions\/Responses"
                    },
                    "action": {
                        "type": "string"
                    }
                }
            },
            "Responses": {
                "type": "object",
                "title": "responses",
                "patternProperties": {
                    "^([0-9]{3})$": {
                        "type": "string"
                    }
                }
            },
            "Action": {
                "type": "object",
                "title": "action",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "status": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string",
                        "pattern": "[a-zA-Z0-9\\-\\_]{3,64}"
                    },
                    "class": {
                        "type": "string"
                    },
                    "engine": {
                        "type": "string"
                    },
                    "config": {
                        "$ref": "#\/definitions\/Config"
                    }
                }
            },
            "Config": {
                "type": "object",
                "title": "config",
                "additionalProperties": {
                    "oneOf": [
                        {
                            "type": "string"
                        },
                        {
                            "type": "number"
                        },
                        {
                            "type": "boolean"
                        },
                        {
                            "type": "null"
                        },
                        {
                            "type": "array",
                            "items": {
                                "oneOf": [
                                    {
                                        "type": "string"
                                    },
                                    {
                                        "type": "number"
                                    },
                                    {
                                        "type": "boolean"
                                    },
                                    {
                                        "type": "null"
                                    }
                                ]
                            },
                            "maxItems": 16
                        }
                    ]
                },
                "maxProperties": 16
            },
            "Source": {
                "type": "object",
                "title": "source",
                "additionalProperties": true
            },
            "Connection": {
                "type": "object",
                "title": "connection",
                "properties": {
                    "id": {
                        "type": "integer"
                    },
                    "name": {
                        "type": "string",
                        "pattern": "[a-zA-Z0-9\\-\\_]{3,64}"
                    },
                    "class": {
                        "type": "string"
                    },
                    "config": {
                        "$ref": "#\/definitions\/Config"
                    }
                }
            },
            "Adapter": {
                "type": "object",
                "title": "adapter",
                "properties": {
                    "actionClass": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    },
                    "connectionClass": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    },
                    "routes": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Routes"
                        }
                    },
                    "action": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Action"
                        }
                    },
                    "schema": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Schema"
                        }
                    },
                    "connection": {
                        "type": "array",
                        "items": {
                            "$ref": "#\/definitions\/Connection"
                        }
                    }
                }
            },
            "POST-request": {
                "$ref": "#\/definitions\/Schema"
            },
            "POST-200-response": {
                "$ref": "#\/definitions\/Adapter"
            }
        }
    },
    "methods": {
        "POST": {
            "request": "#\/definitions\/POST-request",
            "responses": {
                "200": "#\/definitions\/POST-200-response"
            }
        }
    },
    "links": [
        {
            "rel": "openapi",
            "href": "\/export\/openapi\/*\/backend\/import\/:format"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/backend\/import\/:format"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/backend\/import\/:format"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    /**
     * @dataProvider providerSpecs
     */
    public function testPost($spec, $expect)
    {
        $body = new StringStream(json_encode(['schema' => $spec]));

        $response = $this->sendRequest('http://127.0.0.1/backend/import/openapi', 'POST', array(
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
        ];
    }

    protected function getCase01()
    {
        return <<<'JSON'
{
  "openapi": "3.0.0",
  "info": {
    "version": "1.0.0",
    "title": "Swagger Petstore",
    "license": {
      "name": "MIT"
    }
  },
  "servers": [
    {
      "url": "http://petstore.swagger.io/v1"
    }
  ],
  "paths": {
    "/pets": {
      "get": {
        "summary": "List all pets",
        "operationId": "listPets",
        "tags": [
          "pets"
        ],
        "parameters": [
          {
            "name": "limit",
            "in": "query",
            "description": "How many items to return at one time (max 100)",
            "required": false,
            "schema": {
              "type": "integer",
              "format": "int32"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "An paged array of pets",
            "headers": {
              "x-next": {
                "description": "A link to the next page of responses",
                "schema": {
                  "type": "string"
                }
              }
            },
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Pets"
                }
              }
            }
          },
          "default": {
            "description": "unexpected error",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Error"
                }
              }
            }
          }
        }
      },
      "post": {
        "summary": "Create a pet",
        "operationId": "createPets",
        "tags": [
          "pets"
        ],
        "responses": {
          "201": {
            "description": "Null response"
          },
          "default": {
            "description": "unexpected error",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Error"
                }
              }
            }
          }
        }
      }
    },
    "/pets/{petId}": {
      "get": {
        "summary": "Info for a specific pet",
        "operationId": "showPetById",
        "tags": [
          "pets"
        ],
        "parameters": [
          {
            "name": "petId",
            "in": "path",
            "required": true,
            "description": "The id of the pet to retrieve",
            "schema": {
              "type": "string"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "Expected response to a valid request",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Pets"
                }
              }
            }
          },
          "default": {
            "description": "unexpected error",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Error"
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "schemas": {
      "Pet": {
        "title": "Pet",
        "required": [
          "id",
          "name"
        ],
        "properties": {
          "id": {
            "type": "integer",
            "format": "int64"
          },
          "name": {
            "type": "string"
          },
          "tag": {
            "type": "string"
          }
        }
      },
      "Pets": {
        "title": "Pets",
        "type": "array",
        "items": {
          "$ref": "#/components/schemas/Pet"
        }
      },
      "Error": {
        "title": "Error",
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
}
JSON;
    }

    protected function getCase01Expect()
    {
        return <<<'JSON'
{
    "routes": [
        {
            "path": "\/pets",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "parameters": "pets-listPets-GET-query",
                            "responses": {
                                "200": "pets-listPets-GET-200-response"
                            },
                            "action": "pets-listPets-GET"
                        },
                        "POST": {
                            "active": true,
                            "public": true,
                            "action": "pets-createPets-POST"
                        }
                    }
                }
            ]
        },
        {
            "path": "\/pets\/:petId",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "responses": {
                                "200": "pets-_petId_-showPetById-GET-200-response"
                            },
                            "action": "pets-_petId_-showPetById-GET"
                        }
                    }
                }
            ]
        }
    ],
    "action": [
        {
            "name": "pets-listPets-GET",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "statusCode": "200",
                "response": "{\"message\":\"Test implementation\"}"
            }
        },
        {
            "name": "pets-createPets-POST",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "statusCode": "",
                "response": "{\"message\":\"Test implementation\"}"
            }
        },
        {
            "name": "pets-_petId_-showPetById-GET",
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
            "name": "pets-listPets-GET-query",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "type": "object",
                "title": "query",
                "properties": {
                    "limit": {
                        "type": "integer",
                        "format": "int32"
                    }
                }
            }
        },
        {
            "name": "pets-listPets-GET-200-response",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "definitions": {
                    "Pet": {
                        "title": "Pet",
                        "properties": {
                            "id": {
                                "type": "integer",
                                "format": "int64"
                            },
                            "name": {
                                "type": "string"
                            },
                            "tag": {
                                "type": "string"
                            }
                        },
                        "required": [
                            "id",
                            "name"
                        ]
                    }
                },
                "type": "array",
                "title": "Pets",
                "items": {
                    "$ref": "#\/definitions\/Pet"
                }
            }
        },
        {
            "name": "pets-_petId_-showPetById-GET-200-response",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "definitions": {
                    "Pet": {
                        "title": "Pet",
                        "properties": {
                            "id": {
                                "type": "integer",
                                "format": "int64"
                            },
                            "name": {
                                "type": "string"
                            },
                            "tag": {
                                "type": "string"
                            }
                        },
                        "required": [
                            "id",
                            "name"
                        ]
                    }
                },
                "type": "array",
                "title": "Pets",
                "items": {
                    "$ref": "#\/definitions\/Pet"
                }
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
  "openapi": "3.0.0",
  "info": {
    "version": "1.0.0",
    "title": "Swagger Petstore",
    "description": "A sample API that uses a petstore as an example to demonstrate features in the swagger-2.0 specification",
    "termsOfService": "http://swagger.io/terms/",
    "contact": {
      "name": "Swagger API Team",
      "email": "foo@example.com",
      "url": "http://madskristensen.net"
    },
    "license": {
      "name": "MIT",
      "url": "http://github.com/gruntjs/grunt/blob/master/LICENSE-MIT"
    }
  },
  "servers": [
    {
      "url": "http://petstore.swagger.io/api"
    }
  ],
  "paths": {
    "/pets": {
      "get": {
        "description": "Returns all pets from the system that the user has access to\nNam sed condimentum est. Maecenas tempor sagittis sapien, nec rhoncus sem sagittis sit amet. Aenean at gravida augue, ac iaculis sem. Curabitur odio lorem, ornare eget elementum nec, cursus id lectus. Duis mi turpis, pulvinar ac eros ac, tincidunt varius justo. In hac habitasse platea dictumst. Integer at adipiscing ante, a sagittis ligula. Aenean pharetra tempor ante molestie imperdiet. Vivamus id aliquam diam. Cras quis velit non tortor eleifend sagittis. Praesent at enim pharetra urna volutpat venenatis eget eget mauris. In eleifend fermentum facilisis. Praesent enim enim, gravida ac sodales sed, placerat id erat. Suspendisse lacus dolor, consectetur non augue vel, vehicula interdum libero. Morbi euismod sagittis libero sed lacinia.\n\nSed tempus felis lobortis leo pulvinar rutrum. Nam mattis velit nisl, eu condimentum ligula luctus nec. Phasellus semper velit eget aliquet faucibus. In a mattis elit. Phasellus vel urna viverra, condimentum lorem id, rhoncus nibh. Ut pellentesque posuere elementum. Sed a varius odio. Morbi rhoncus ligula libero, vel eleifend nunc tristique vitae. Fusce et sem dui. Aenean nec scelerisque tortor. Fusce malesuada accumsan magna vel tempus. Quisque mollis felis eu dolor tristique, sit amet auctor felis gravida. Sed libero lorem, molestie sed nisl in, accumsan tempor nisi. Fusce sollicitudin massa ut lacinia mattis. Sed vel eleifend lorem. Pellentesque vitae felis pretium, pulvinar elit eu, euismod sapien.\n",
        "operationId": "findPets",
        "parameters": [
          {
            "name": "tags",
            "in": "query",
            "description": "tags to filter by",
            "required": false,
            "style": "form",
            "schema": {
              "type": "array",
              "items": {
                "type": "string"
              }
            }
          },
          {
            "name": "limit",
            "in": "query",
            "description": "maximum number of results to return",
            "required": false,
            "schema": {
              "type": "integer",
              "format": "int32"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "pet response",
            "content": {
              "application/json": {
                "schema": {
                  "type": "array",
                  "items": {
                    "$ref": "#/components/schemas/Pet"
                  }
                }
              }
            }
          },
          "default": {
            "description": "unexpected error",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Error"
                }
              }
            }
          }
        }
      },
      "post": {
        "description": "Creates a new pet in the store.  Duplicates are allowed",
        "operationId": "addPet",
        "requestBody": {
          "description": "Pet to add to the store",
          "required": true,
          "content": {
            "application/json": {
              "schema": {
                "$ref": "#/components/schemas/NewPet"
              }
            }
          }
        },
        "responses": {
          "200": {
            "description": "pet response",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Pet"
                }
              }
            }
          },
          "default": {
            "description": "unexpected error",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Error"
                }
              }
            }
          }
        }
      }
    },
    "/pets/{id}": {
      "get": {
        "description": "Returns a user based on a single ID, if the user does not have access to the pet",
        "operationId": "find pet by id",
        "parameters": [
          {
            "name": "id",
            "in": "path",
            "description": "ID of pet to fetch",
            "required": true,
            "schema": {
              "type": "integer",
              "format": "int64"
            }
          }
        ],
        "responses": {
          "200": {
            "description": "pet response",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Pet"
                }
              }
            }
          },
          "default": {
            "description": "unexpected error",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Error"
                }
              }
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
            "schema": {
              "type": "integer",
              "format": "int64"
            }
          }
        ],
        "responses": {
          "204": {
            "description": "pet deleted"
          },
          "default": {
            "description": "unexpected error",
            "content": {
              "application/json": {
                "schema": {
                  "$ref": "#/components/schemas/Error"
                }
              }
            }
          }
        }
      }
    }
  },
  "components": {
    "schemas": {
      "Pet": {
        "title": "Pet",
        "allOf": [
          {
            "$ref": "#/components/schemas/NewPet"
          },
          {
            "title": "PetId",
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
        "title": "NewPet",
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
      "Error": {
        "title": "Error",
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
}
JSON;
    }

    protected function getCase02Expect()
    {
        return <<<'JSON'
{
    "routes": [
        {
            "path": "\/pets",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "parameters": "pets-findPets-GET-query",
                            "responses": {
                                "200": "pets-findPets-GET-200-response"
                            },
                            "action": "pets-findPets-GET"
                        },
                        "POST": {
                            "active": true,
                            "public": true,
                            "request": "pets-addPet-POST-request",
                            "responses": {
                                "200": "pets-addPet-POST-200-response"
                            },
                            "action": "pets-addPet-POST"
                        }
                    }
                }
            ]
        },
        {
            "path": "\/pets\/:id",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "responses": {
                                "200": "pets-_id_-find_pet_by_id-GET-200-response"
                            },
                            "action": "pets-_id_-find_pet_by_id-GET"
                        },
                        "DELETE": {
                            "active": true,
                            "public": true,
                            "action": "pets-_id_-deletePet-DELETE"
                        }
                    }
                }
            ]
        }
    ],
    "action": [
        {
            "name": "pets-findPets-GET",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "statusCode": "200",
                "response": "{\"message\":\"Test implementation\"}"
            }
        },
        {
            "name": "pets-addPet-POST",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "statusCode": "200",
                "response": "{\"message\":\"Test implementation\"}"
            }
        },
        {
            "name": "pets-_id_-find_pet_by_id-GET",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "engine": "Fusio\\Engine\\Factory\\Resolver\\PhpClass",
            "config": {
                "statusCode": "200",
                "response": "{\"message\":\"Test implementation\"}"
            }
        },
        {
            "name": "pets-_id_-deletePet-DELETE",
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
            "name": "pets-findPets-GET-query",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "type": "object",
                "title": "query",
                "properties": {
                    "tags": {
                        "type": "array",
                        "items": {
                            "type": "string"
                        }
                    },
                    "limit": {
                        "type": "integer",
                        "format": "int32"
                    }
                }
            }
        },
        {
            "name": "pets-findPets-GET-200-response",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "definitions": {
                    "NewPet": {
                        "title": "NewPet",
                        "properties": {
                            "name": {
                                "type": "string"
                            },
                            "tag": {
                                "type": "string"
                            }
                        },
                        "required": [
                            "name"
                        ]
                    },
                    "PetId": {
                        "title": "PetId",
                        "properties": {
                            "id": {
                                "type": "integer",
                                "format": "int64"
                            }
                        },
                        "required": [
                            "id"
                        ]
                    }
                },
                "type": "array",
                "items": {
                    "title": "Pet",
                    "allOf": [
                        {
                            "$ref": "#\/definitions\/NewPet"
                        },
                        {
                            "$ref": "#\/definitions\/PetId"
                        }
                    ]
                }
            }
        },
        {
            "name": "pets-addPet-POST-request",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "title": "NewPet",
                "properties": {
                    "name": {
                        "type": "string"
                    },
                    "tag": {
                        "type": "string"
                    }
                },
                "required": [
                    "name"
                ]
            }
        },
        {
            "name": "pets-addPet-POST-200-response",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "definitions": {
                    "NewPet": {
                        "title": "NewPet",
                        "properties": {
                            "name": {
                                "type": "string"
                            },
                            "tag": {
                                "type": "string"
                            }
                        },
                        "required": [
                            "name"
                        ]
                    },
                    "PetId": {
                        "title": "PetId",
                        "properties": {
                            "id": {
                                "type": "integer",
                                "format": "int64"
                            }
                        },
                        "required": [
                            "id"
                        ]
                    }
                },
                "title": "Pet",
                "allOf": [
                    {
                        "$ref": "#\/definitions\/NewPet"
                    },
                    {
                        "$ref": "#\/definitions\/PetId"
                    }
                ]
            }
        },
        {
            "name": "pets-_id_-find_pet_by_id-GET-200-response",
            "source": {
                "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
                "id": "urn:schema.phpsx.org#",
                "definitions": {
                    "NewPet": {
                        "title": "NewPet",
                        "properties": {
                            "name": {
                                "type": "string"
                            },
                            "tag": {
                                "type": "string"
                            }
                        },
                        "required": [
                            "name"
                        ]
                    },
                    "PetId": {
                        "title": "PetId",
                        "properties": {
                            "id": {
                                "type": "integer",
                                "format": "int64"
                            }
                        },
                        "required": [
                            "id"
                        ]
                    }
                },
                "title": "Pet",
                "allOf": [
                    {
                        "$ref": "#\/definitions\/NewPet"
                    },
                    {
                        "$ref": "#\/definitions\/PetId"
                    }
                ]
            }
        }
    ]
}
JSON;
    }
}

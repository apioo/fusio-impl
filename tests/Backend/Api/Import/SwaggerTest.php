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
            [$this->getCase03(), $this->getCase03Expect()],
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
                            "parameters": "Passthru",
                            "request": "Passthru",
                            "responses": {
                                "200": "findPets-GET-200-response"
                            },
                            "action": "Welcome"
                        },
                        "POST": {
                            "active": true,
                            "public": true,
                            "parameters": "Passthru",
                            "request": "addPet-POST-request",
                            "responses": {
                                "200": "addPet-POST-200-response"
                            },
                            "action": "Welcome"
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
                            "parameters": "Passthru",
                            "request": "Passthru",
                            "responses": {
                                "200": "findPetById-GET-200-response"
                            },
                            "action": "Welcome"
                        },
                        "DELETE": {
                            "active": true,
                            "public": true,
                            "parameters": "Passthru",
                            "request": "Passthru",
                            "responses": {
                                "200": "Passthru"
                            },
                            "action": "Welcome"
                        }
                    }
                }
            ]
        }
    ],
    "schema": [
        {
            "name": "findPets-GET-200-response",
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
            "name": "addPet-POST-200-response",
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
            "name": "findPetById-GET-200-response",
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
                            "parameters": "Passthru",
                            "request": "Passthru",
                            "responses": {
                                "200": "Passthru"
                            },
                            "action": "listVersionsv2"
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
                            "parameters": "Passthru",
                            "request": "Passthru",
                            "responses": {
                                "200": "Passthru"
                            },
                            "action": "getVersionDetailsv2"
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

    protected function getCase03()
    {
        return <<<'JSON'
{
    "swagger": "2.0",
    "info": {
        "version": "1",
        "title": "DB Fahrplan API",
        "description": "Deutsche Bahn Fahrplan API",
        "termsOfService": "http://data.deutschebahn.com/nutzungsbedingungen.html",
        "contact": {
            "name": "Michael Binzen",
            "email": "michael.binzen@deutschebahn.com"
        }
    },
    "schemes": [
        "https"
    ],
    "host": "open-api.bahn.de",
    "basePath": "/bin/rest.exe",
    "consumes": [
        "application/x-www-form-urlencoded",
        "application/json"
    ],
    "securityDefinitions": {
        "authKey": {
            "type": "apiKey",
            "in": "query",
            "name": "authKey"
        }
    },
    "paths": {
        "/location.name": {
            "get": {
                "description": "The location.name service can be used to perform a pattern matching of a user input and to retrieve a list of possible matches in the journey planner database. Possible matches might be stops/stations, points of interest and addresses.",
                "security": [
                    {
                        "authKey": []
                    }
                ],
                "consumes": [
                    "application/json"
                ],
                "produces": [
                    "application/json"
                ],
                "parameters": [
                    {
                        "name": "format",
                        "description": "The interface returns responses either in XML (default) or JSON format.",
                        "in": "query",
                        "required": true,
                        "type": "string",
                        "enum": [
                            "json"
                        ]
                    },
                    {
                        "name": "lang",
                        "description": "The REST API supports multiple languages. The default language is English and it is used if no language parameter is delivered. The language code has to be lower case. The supported languages depend on the plan data of the underlying system. The chosen language only influences the returned Notes in the REST responses.",
                        "in": "query",
                        "required": false,
                        "type": "string",
                        "default": "en",
                        "enum": [
                            "en",
                            "de",
                            "fr",
                            "da",
                            "pl",
                            "it",
                            "es",
                            "nl"
                        ]
                    },
                    {
                        "name": "input",
                        "description": "This parameter contains a string with the user input.",
                        "in": "query",
                        "required": true,
                        "type": "string"
                    }
                ],
                "responses": {
                    "200": {
                        "description": "The result is a list of possible matches (locations) where the user might pick one entry to perform a trip request with this location as origin or destination or to ask for a departure board or arrival board of this location (stops/stations only).",
                        "schema": {
                            "$ref": "#/definitions/LocationResponse"
                        }
                    }
                }
            }
        },
        "/departureBoard": {
            "get": {
                "description": "Retrieves the station board for the given station. This method will return the next 20 departures (or less if not existing) from a given point in time. The service can only be called for stops/stations by using according ID retrieved by the location.name method.",
                "security": [
                    {
                        "authKey": []
                    }
                ],
                "consumes": [
                    "application/json"
                ],
                "produces": [
                    "application/json"
                ],
                "parameters": [
                    {
                        "name": "format",
                        "description": "The interface returns responses either in XML (default) or JSON format.",
                        "in": "query",
                        "required": true,
                        "type": "string",
                        "enum": [
                            "json"
                        ]
                    },
                    {
                        "name": "lang",
                        "description": "The REST API supports multiple languages. The default language is English and it is used if no language parameter is delivered. The language code has to be lower case. The supported languages depend on the plan data of the underlying system. The chosen language only influences the returned Notes in the REST responses.",
                        "in": "query",
                        "required": false,
                        "type": "string",
                        "default": "en",
                        "enum": [
                            "en",
                            "de",
                            "fr",
                            "da",
                            "pl",
                            "it",
                            "es",
                            "nl"
                        ]
                    },
                    {
                        "name": "id",
                        "description": "Id of the stop/station. The service can only be called for stops/stations by using according id retrieved by the location method.",
                        "in": "query",
                        "required": true,
                        "type": "string"
                    },
                    {
                        "name": "date",
                        "description": "The date of departures.",
                        "in": "query",
                        "required": true,
                        "type": "string"
                    },
                    {
                        "name": "time",
                        "description": "The time of departures.",
                        "in": "query",
                        "required": true,
                        "type": "string"
                    }
                ],
                "responses": {
                    "200": {
                        "description": "The next 20 departures (or less if not existing) from a given point in time.",
                        "schema": {
                            "$ref": "#/definitions/DepartureBoardResponse"
                        }
                    }
                }
            }
        },
        "/arrivalBoard": {
            "get": {
                "description": "Retrieves the station board for the given station. This method will return the next 20 arrivals (or less if not existing) from a given point in time. The service can only be called for stops/stations by using according ID retrieved by the location.name method.",
                "security": [
                    {
                        "authKey": []
                    }
                ],
                "consumes": [
                    "application/json"
                ],
                "produces": [
                    "application/json"
                ],
                "parameters": [
                    {
                        "name": "format",
                        "description": "The interface returns responses either in XML (default) or JSON format.",
                        "in": "query",
                        "required": true,
                        "type": "string",
                        "enum": [
                            "json"
                        ]
                    },
                    {
                        "name": "lang",
                        "description": "The REST API supports multiple languages. The default language is English and it is used if no language parameter is delivered. The language code has to be lower case. The supported languages depend on the plan data of the underlying system. The chosen language only influences the returned Notes in the REST responses.",
                        "in": "query",
                        "required": false,
                        "type": "string",
                        "default": "en",
                        "enum": [
                            "en",
                            "de",
                            "fr",
                            "da",
                            "pl",
                            "it",
                            "es",
                            "nl"
                        ]
                    },
                    {
                        "name": "id",
                        "description": "Id of the stop/station. The service can only be called for stops/stations by using according id retrieved by the location method.",
                        "in": "query",
                        "required": true,
                        "type": "string"
                    },
                    {
                        "name": "date",
                        "description": "The date of arrivals.",
                        "in": "query",
                        "required": true,
                        "type": "string"
                    },
                    {
                        "name": "time",
                        "description": "The time of arrivals.",
                        "in": "query",
                        "required": true,
                        "type": "string"
                    }
                ],
                "responses": {
                    "200": {
                        "description": "The next 20 arrivals (or less if not existing) from a given point in time.",
                        "schema": {
                            "$ref": "#/definitions/ArrivalBoardResponse"
                        }
                    }
                }
            }
        },
        "/journeyDetail": {
            "get": {
                "description": "Delivers information about the complete route of a vehicle. This service can't be called directly but only by reference URLs in a result of a departureBoard request. It contains a list of all stops/stations of this journey including all departure and arrival times (with realtime data if available / not supported right now) and additional information like specific attributes about facilities and other texts.",
                "consumes": [
                    "application/json"
                ],
                "produces": [
                    "application/json"
                ],
                "parameters": [
                    {
                        "name": "format",
                        "description": "The interface returns responses either in XML (default) or JSON format.",
                        "in": "query",
                        "required": true,
                        "type": "string",
                        "enum": [
                            "json"
                        ]
                    },
                    {
                        "name": "lang",
                        "description": "The REST API supports multiple languages. The default language is English and it is used if no language parameter is delivered. The language code has to be lower case. The supported languages depend on the plan data of the underlying system. The chosen language only influences the returned Notes in the REST responses.",
                        "in": "query",
                        "required": false,
                        "type": "string",
                        "default": "en",
                        "enum": [
                            "en",
                            "de",
                            "fr",
                            "da",
                            "pl",
                            "it",
                            "es",
                            "nl"
                        ]
                    },
                    {
                        "name": "ref",
                        "description": "Reference identifier.",
                        "in": "query",
                        "required": true,
                        "type": "string"
                    }
                ],
                "responses": {
                    "200": {
                        "description": "List of all stops/stations of this journey including all departure and arrival times (with realtime data if available / not supported right now) and additional information like specific attributes about facilities and other texts.",
                        "schema": {
                            "$ref": "#/definitions/JourneyDetailResponse"
                        }
                    }
                }
            }
        }
    },
    "definitions": {
        "LocationResponse": {
            "type": "object",
            "required": [
                "LocationList"
            ],
            "properties": {
                "LocationList": {
                    "$ref": "#/definitions/LocationList"
                }
            }
        },
        "LocationList": {
            "type": "object",
            "required": [
                "StopLocation"
            ],
            "properties": {
                "StopLocation": {
                    "type": "array",
                    "items": {
                        "$ref": "#/definitions/StopLocation"
                    }
                }
            }
        },
        "StopLocation": {
            "type": "object",
            "required": [
                "name",
                "lon",
                "lat",
                "id"
            ],
            "properties": {
                "id": {
                    "type": "integer",
                    "format": "int32"
                },
                "name": {
                    "type": "string"
                },
                "lon": {
                    "type": "number",
                    "format": "double"
                },
                "lat": {
                    "type": "number",
                    "format": "double"
                }
            }
        },
        "DepartureBoardResponse": {
            "type": "object",
            "required": [
                "DepartureBoard"
            ],
            "properties": {
                "DepartureBoard": {
                    "$ref": "#/definitions/DepartureBoard"
                }
            }
        },
        "DepartureBoard": {
            "type": "object",
            "required": [
                "Departure"
            ],
            "properties": {
                "Departure": {
                    "type": "array",
                    "items": {
                        "$ref": "#/definitions/DepartureOrArrival"
                    }
                }
            }
        },
        "ArrivalBoardResponse": {
            "type": "object",
            "required": [
                "DepartureBoard"
            ],
            "properties": {
                "DepartureBoard": {
                    "$ref": "#/definitions/ArrivalBoard"
                }
            }
        },
        "ArrivalBoard": {
            "type": "object",
            "required": [
                "Arrival"
            ],
            "properties": {
                "Arrival": {
                    "type": "array",
                    "items": {
                        "$ref": "#/definitions/DepartureOrArrival"
                    }
                }
            }
        },
        "DepartureOrArrival": {
            "type": "object",
            "required": [
                "name",
                "type",
                "stopid",
                "stop",
                "time",
                "date",
                "direction",
                "track",
                "JourneyDetailRef"
            ],
            "properties": {
                "name": {
                    "type": "string"
                },
                "type": {
                    "type": "string"
                },
                "stopid": {
                    "type": "integer",
                    "format": "int32"
                },
                "stop": {
                    "type": "string"
                },
                "time": {
                    "$ref": "#/definitions/LocalTime"
                },
                "date": {
                    "$ref": "#/definitions/LocalDate"
                },
                "direction": {
                    "type": "string"
                },
                "track": {
                    "type": "string"
                },
                "JourneyDetailRef": {
                    "$ref": "#/definitions/JourneyDetailRef"
                }
            }
        },
        "JourneyDetailRef": {
            "type": "object",
            "required": [
                "ref"
            ],
            "properties": {
                "ref": {
                    "type": "string"
                }
            }
        },
        "JourneyDetailResponse": {
            "type": "object",
            "required": [
                "JourneyDetail"
            ],
            "properties": {
                "JourneyDetail": {
                    "$ref": "#/definitions/JourneyDetail"
                }
            }
        },
        "JourneyDetail": {
            "type": "object",
            "required": [
                "Stops",
                "Names",
                "Types",
                "Operators",
                "Notes"
            ],
            "properties": {
                "Stops": {
                    "$ref": "#/definitions/Stops"
                },
                "Names": {
                    "$ref": "#/definitions/Names"
                },
                "Types": {
                    "$ref": "#/definitions/Types"
                },
                "Operators": {
                    "$ref": "#/definitions/Operators"
                },
                "Notes": {
                    "$ref": "#/definitions/Notes"
                }
            }
        },
        "Stops": {
            "type": "object",
            "required": [
                "Stop"
            ],
            "properties": {
                "Stop": {
                    "type": "array",
                    "items": {
                        "$ref": "#/definitions/Stop"
                    }
                }
            }
        },
        "Stop": {
            "type": "object",
            "required": [
                "id",
                "name",
                "lon",
                "lat",
                "routeIdx",
                "depTime",
                "depDate",
                "track"
            ],
            "properties": {
                "id": {
                    "type": "integer",
                    "format": "int32"
                },
                "name": {
                    "type": "string"
                },
                "lon": {
                    "type": "number",
                    "format": "double"
                },
                "lat": {
                    "type": "number",
                    "format": "double"
                },
                "routeIdx": {
                    "type": "integer",
                    "format": "int32"
                },
                "depTime": {
                    "$ref": "#/definitions/LocalTime"
                },
                "depDate": {
                    "$ref": "#/definitions/LocalDate"
                },
                "track": {
                    "type": "string"
                }
            }
        },
        "Names": {
            "type": "object",
            "required": [
                "Name"
            ],
            "properties": {
                "Name": {
                    "type": "array",
                    "items": {
                        "$ref": "#/definitions/Name"
                    }
                }
            }
        },
        "Name": {
            "type": "object",
            "required": [
                "name",
                "routeIdxFrom",
                "routeIdxTo"
            ],
            "properties": {
                "name": {
                    "type": "string"
                },
                "routeIdxFrom": {
                    "type": "integer",
                    "format": "int32"
                },
                "routeIdxTo": {
                    "type": "integer",
                    "format": "int32"
                }
            }
        },
        "Types": {
            "type": "object",
            "required": [
                "Type"
            ],
            "properties": {
                "Type": {
                    "type": "array",
                    "items": {
                        "$ref": "#/definitions/Type"
                    }
                }
            }
        },
        "Type": {
            "type": "object",
            "required": [
                "type",
                "routeIdxFrom",
                "routeIdxTo"
            ],
            "properties": {
                "type": {
                    "type": "string"
                },
                "routeIdxFrom": {
                    "type": "integer",
                    "format": "int32"
                },
                "routeIdxTo": {
                    "type": "integer",
                    "format": "int32"
                }
            }
        },
        "Operators": {
            "type": "object",
            "required": [
                "Operator"
            ],
            "properties": {
                "Operator": {
                    "type": "array",
                    "items": {
                        "$ref": "#/definitions/Operator"
                    }
                }
            }
        },
        "Operator": {
            "type": "object",
            "required": [
                "name",
                "routeIdxFrom",
                "routeIdxTo"
            ],
            "properties": {
                "name": {
                    "type": "string"
                },
                "routeIdxFrom": {
                    "type": "integer",
                    "format": "int32"
                },
                "routeIdxTo": {
                    "type": "integer",
                    "format": "int32"
                }
            }
        },
        "Notes": {
            "type": "object",
            "required": [
                "Note"
            ],
            "properties": {
                "Note": {
                    "type": "array",
                    "items": {
                        "$ref": "#/definitions/Note"
                    }
                }
            }
        },
        "Note": {
            "type": "object",
            "required": [
                "key",
                "priority",
                "routeIdxFrom",
                "routeIdxTo",
                "$"
            ],
            "properties": {
                "key": {
                    "type": "string"
                },
                "priority": {
                    "type": "integer",
                    "format": "int32"
                },
                "routeIdxFrom": {
                    "type": "integer",
                    "format": "int32"
                },
                "routeIdxTo": {
                    "type": "integer",
                    "format": "int32"
                },
                "$": {
                    "type": "string"
                }
            }
        },
        "LocalTime": {
            "type": "string"
        },
        "LocalDate": {
            "type": "string"
        }
    }
}
JSON;
    }

    protected function getCase03Expect()
    {
        return <<<'JSON'
{
    "routes": [
        {
            "path": "\/bin\/rest.exe\/location.name",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "parameters": "Passthru",
                            "request": "Passthru",
                            "responses": {
                                "200": "bin-restexe-locationname-GET-200-response"
                            },
                            "action": "Welcome"
                        }
                    }
                }
            ]
        },
        {
            "path": "\/bin\/rest.exe\/departureBoard",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "parameters": "Passthru",
                            "request": "Passthru",
                            "responses": {
                                "200": "bin-restexe-departureBoard-GET-200-response"
                            },
                            "action": "Welcome"
                        }
                    }
                }
            ]
        },
        {
            "path": "\/bin\/rest.exe\/arrivalBoard",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "parameters": "Passthru",
                            "request": "Passthru",
                            "responses": {
                                "200": "bin-restexe-arrivalBoard-GET-200-response"
                            },
                            "action": "Welcome"
                        }
                    }
                }
            ]
        },
        {
            "path": "\/bin\/rest.exe\/journeyDetail",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "parameters": "Passthru",
                            "request": "Passthru",
                            "responses": {
                                "200": "bin-restexe-journeyDetail-GET-200-response"
                            },
                            "action": "Welcome"
                        }
                    }
                }
            ]
        }
    ],
    "schema": [
        {
            "name": "bin-restexe-locationname-GET-200-response",
            "source": {
                "type": "object",
                "required": [
                    "LocationList"
                ],
                "properties": {
                    "LocationList": {
                        "type": "object",
                        "required": [
                            "StopLocation"
                        ],
                        "properties": {
                            "StopLocation": {
                                "type": "array",
                                "items": {
                                    "type": "object",
                                    "required": [
                                        "name",
                                        "lon",
                                        "lat",
                                        "id"
                                    ],
                                    "properties": {
                                        "id": {
                                            "type": "integer",
                                            "format": "int32"
                                        },
                                        "name": {
                                            "type": "string"
                                        },
                                        "lon": {
                                            "type": "number",
                                            "format": "double"
                                        },
                                        "lat": {
                                            "type": "number",
                                            "format": "double"
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        {
            "name": "bin-restexe-departureBoard-GET-200-response",
            "source": {
                "type": "object",
                "required": [
                    "DepartureBoard"
                ],
                "properties": {
                    "DepartureBoard": {
                        "type": "object",
                        "required": [
                            "Departure"
                        ],
                        "properties": {
                            "Departure": {
                                "type": "array",
                                "items": {
                                    "type": "object",
                                    "required": [
                                        "name",
                                        "type",
                                        "stopid",
                                        "stop",
                                        "time",
                                        "date",
                                        "direction",
                                        "track",
                                        "JourneyDetailRef"
                                    ],
                                    "properties": {
                                        "name": {
                                            "type": "string"
                                        },
                                        "type": {
                                            "type": "string"
                                        },
                                        "stopid": {
                                            "type": "integer",
                                            "format": "int32"
                                        },
                                        "stop": {
                                            "type": "string"
                                        },
                                        "time": {
                                            "type": "string"
                                        },
                                        "date": {
                                            "type": "string"
                                        },
                                        "direction": {
                                            "type": "string"
                                        },
                                        "track": {
                                            "type": "string"
                                        },
                                        "JourneyDetailRef": {
                                            "type": "object",
                                            "required": [
                                                "ref"
                                            ],
                                            "properties": {
                                                "ref": {
                                                    "type": "string"
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        {
            "name": "bin-restexe-arrivalBoard-GET-200-response",
            "source": {
                "type": "object",
                "required": [
                    "DepartureBoard"
                ],
                "properties": {
                    "DepartureBoard": {
                        "type": "object",
                        "required": [
                            "Arrival"
                        ],
                        "properties": {
                            "Arrival": {
                                "type": "array",
                                "items": {
                                    "type": "object",
                                    "required": [
                                        "name",
                                        "type",
                                        "stopid",
                                        "stop",
                                        "time",
                                        "date",
                                        "direction",
                                        "track",
                                        "JourneyDetailRef"
                                    ],
                                    "properties": {
                                        "name": {
                                            "type": "string"
                                        },
                                        "type": {
                                            "type": "string"
                                        },
                                        "stopid": {
                                            "type": "integer",
                                            "format": "int32"
                                        },
                                        "stop": {
                                            "type": "string"
                                        },
                                        "time": {
                                            "type": "string"
                                        },
                                        "date": {
                                            "type": "string"
                                        },
                                        "direction": {
                                            "type": "string"
                                        },
                                        "track": {
                                            "type": "string"
                                        },
                                        "JourneyDetailRef": {
                                            "type": "object",
                                            "required": [
                                                "ref"
                                            ],
                                            "properties": {
                                                "ref": {
                                                    "type": "string"
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        },
        {
            "name": "bin-restexe-journeyDetail-GET-200-response",
            "source": {
                "type": "object",
                "required": [
                    "JourneyDetail"
                ],
                "properties": {
                    "JourneyDetail": {
                        "type": "object",
                        "required": [
                            "Stops",
                            "Names",
                            "Types",
                            "Operators",
                            "Notes"
                        ],
                        "properties": {
                            "Stops": {
                                "type": "object",
                                "required": [
                                    "Stop"
                                ],
                                "properties": {
                                    "Stop": {
                                        "type": "array",
                                        "items": {
                                            "type": "object",
                                            "required": [
                                                "id",
                                                "name",
                                                "lon",
                                                "lat",
                                                "routeIdx",
                                                "depTime",
                                                "depDate",
                                                "track"
                                            ],
                                            "properties": {
                                                "id": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                },
                                                "name": {
                                                    "type": "string"
                                                },
                                                "lon": {
                                                    "type": "number",
                                                    "format": "double"
                                                },
                                                "lat": {
                                                    "type": "number",
                                                    "format": "double"
                                                },
                                                "routeIdx": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                },
                                                "depTime": {
                                                    "type": "string"
                                                },
                                                "depDate": {
                                                    "type": "string"
                                                },
                                                "track": {
                                                    "type": "string"
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                            "Names": {
                                "type": "object",
                                "required": [
                                    "Name"
                                ],
                                "properties": {
                                    "Name": {
                                        "type": "array",
                                        "items": {
                                            "type": "object",
                                            "required": [
                                                "name",
                                                "routeIdxFrom",
                                                "routeIdxTo"
                                            ],
                                            "properties": {
                                                "name": {
                                                    "type": "string"
                                                },
                                                "routeIdxFrom": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                },
                                                "routeIdxTo": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                            "Types": {
                                "type": "object",
                                "required": [
                                    "Type"
                                ],
                                "properties": {
                                    "Type": {
                                        "type": "array",
                                        "items": {
                                            "type": "object",
                                            "required": [
                                                "type",
                                                "routeIdxFrom",
                                                "routeIdxTo"
                                            ],
                                            "properties": {
                                                "type": {
                                                    "type": "string"
                                                },
                                                "routeIdxFrom": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                },
                                                "routeIdxTo": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                            "Operators": {
                                "type": "object",
                                "required": [
                                    "Operator"
                                ],
                                "properties": {
                                    "Operator": {
                                        "type": "array",
                                        "items": {
                                            "type": "object",
                                            "required": [
                                                "name",
                                                "routeIdxFrom",
                                                "routeIdxTo"
                                            ],
                                            "properties": {
                                                "name": {
                                                    "type": "string"
                                                },
                                                "routeIdxFrom": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                },
                                                "routeIdxTo": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                }
                                            }
                                        }
                                    }
                                }
                            },
                            "Notes": {
                                "type": "object",
                                "required": [
                                    "Note"
                                ],
                                "properties": {
                                    "Note": {
                                        "type": "array",
                                        "items": {
                                            "type": "object",
                                            "required": [
                                                "key",
                                                "priority",
                                                "routeIdxFrom",
                                                "routeIdxTo",
                                                "$"
                                            ],
                                            "properties": {
                                                "key": {
                                                    "type": "string"
                                                },
                                                "priority": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                },
                                                "routeIdxFrom": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                },
                                                "routeIdxTo": {
                                                    "type": "integer",
                                                    "format": "int32"
                                                },
                                                "$": {
                                                    "type": "string"
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    ]
}
JSON;
    }
}

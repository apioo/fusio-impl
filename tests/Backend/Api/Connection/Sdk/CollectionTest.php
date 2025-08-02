<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\Backend\Api\Connection\Sdk;

use Fusio\Impl\Tests\DbTestCase;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/backend/connection/StarwarsSDK/sdk', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "baseUrl": "https:\/\/swapi.dev\/api",
    "imports": [],
    "operations": [
        {
            "name": "people.getAll",
            "description": "Get all the people",
            "httpMethod": "GET",
            "httpPath": "\/people",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "search",
                    "in": "query",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "People_Collection",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "people.get",
            "description": "Get a specific people",
            "httpMethod": "GET",
            "httpPath": "\/people\/:id",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "id",
                    "in": "path",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "People",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "film.getAll",
            "description": "Get all the films",
            "httpMethod": "GET",
            "httpPath": "\/films",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "search",
                    "in": "query",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "Film_Collection",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "film.get",
            "description": "Get a specific film",
            "httpMethod": "GET",
            "httpPath": "\/films\/:id",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "id",
                    "in": "path",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "Film",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "starship.getAll",
            "description": "Get all the starships",
            "httpMethod": "GET",
            "httpPath": "\/starships",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "search",
                    "in": "query",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "Starship_Collection",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "starship.get",
            "description": "Get a specific starship",
            "httpMethod": "GET",
            "httpPath": "\/starships\/:id",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "id",
                    "in": "path",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "Starship",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "species.getAll",
            "description": "Get all the species",
            "httpMethod": "GET",
            "httpPath": "\/species",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "search",
                    "in": "query",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "Species_Collection",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "species.get",
            "description": "Get a specific species",
            "httpMethod": "GET",
            "httpPath": "\/species\/:id",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "id",
                    "in": "path",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "Species",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "vehicle.getAll",
            "description": "Get all the vehicles",
            "httpMethod": "GET",
            "httpPath": "\/vehicles",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "search",
                    "in": "query",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "Vehicle_Collection",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "vehicle.get",
            "description": "Get a specific vehicle",
            "httpMethod": "GET",
            "httpPath": "\/vehicles\/:id",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "id",
                    "in": "path",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "Vehicle",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "planet.getAll",
            "description": "Get all the planets",
            "httpMethod": "GET",
            "httpPath": "\/planets",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "search",
                    "in": "query",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "Planet_Collection",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        {
            "name": "planet.get",
            "description": "Get a specific planet",
            "httpMethod": "GET",
            "httpPath": "\/planets\/:id",
            "httpCode": 200,
            "arguments": [
                {
                    "name": "id",
                    "in": "path",
                    "type": "string"
                }
            ],
            "throws": [],
            "return": "Planet",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        }
    ],
    "types": [
        {
            "name": "Collection",
            "type": "struct",
            "description": "",
            "properties": [
                {
                    "name": "count",
                    "description": "",
                    "type": "integer"
                },
                {
                    "name": "next",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "previous",
                    "description": "",
                    "type": "string"
                }
            ]
        },
        {
            "name": "Film",
            "type": "struct",
            "description": "A Film is a single film",
            "properties": [
                {
                    "name": "title",
                    "description": "The title of this film",
                    "type": "string"
                },
                {
                    "name": "episode_id",
                    "description": "The episode number of this film",
                    "type": "integer"
                },
                {
                    "name": "opening_crawl",
                    "description": "The opening paragraphs at the beginning of this film",
                    "type": "string"
                },
                {
                    "name": "director",
                    "description": "The name of the director of this film",
                    "type": "string"
                },
                {
                    "name": "producer",
                    "description": "The name(s) of the producer(s) of this film. Comma separated",
                    "type": "string"
                },
                {
                    "name": "release_date",
                    "description": "The ISO 8601 date format of film release at original creator country",
                    "type": "string",
                    "format": "date"
                },
                {
                    "name": "species",
                    "description": "An array of species resource URLs that are in this film",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "starships",
                    "description": "An array of starship resource URLs that are in this film",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "vehicles",
                    "description": "An array of vehicle resource URLs that are in this film",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "characters",
                    "description": "An array of people resource URLs that are in this film",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "planets",
                    "description": "An array of planet resource URLs that are in this film",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "url",
                    "description": "The hypermedia URL of this resource",
                    "type": "string"
                },
                {
                    "name": "created",
                    "description": "The ISO 8601 date format of the time that this resource was created",
                    "type": "string",
                    "format": "date-time"
                },
                {
                    "name": "edited",
                    "description": "The ISO 8601 date format of the time that this resource was edited",
                    "type": "string",
                    "format": "date-time"
                }
            ]
        },
        {
            "name": "Film_Collection",
            "type": "struct",
            "description": "",
            "parent": "Collection",
            "properties": [
                {
                    "name": "results",
                    "description": "",
                    "type": "array",
                    "reference": "Film"
                }
            ]
        },
        {
            "name": "People",
            "type": "struct",
            "description": "A People is an individual person or character within the Star Wars universe",
            "properties": [
                {
                    "name": "name",
                    "description": "The name of this person.",
                    "type": "string"
                },
                {
                    "name": "birth_year",
                    "description": "The birth year of the person, using the in-universe standard of BBY or ABY - Before the Battle of Yavin or After the Battle of Yavin. The Battle of Yavin is a battle that occurs at the end of Star Wars episode IV: A New Hope.",
                    "type": "string"
                },
                {
                    "name": "eye_color",
                    "description": "The eye color of this person. Will be \"unknown\" if not known or \"n\/a\" if the person does not have an eye.",
                    "type": "string"
                },
                {
                    "name": "gender",
                    "description": "The gender of this person. Either \"Male\", \"Female\" or \"unknown\", \"n\/a\" if the person does not have a gender.",
                    "type": "string"
                },
                {
                    "name": "hair_color",
                    "description": "The hair color of this person. Will be \"unknown\" if not known or \"n\/a\" if the person does not have hair.",
                    "type": "string"
                },
                {
                    "name": "height",
                    "description": "The height of the person in centimeters.",
                    "type": "string"
                },
                {
                    "name": "mass",
                    "description": "The mass of the person in kilograms.",
                    "type": "string"
                },
                {
                    "name": "skin_color",
                    "description": "The skin color of this person.",
                    "type": "string"
                },
                {
                    "name": "homeworld",
                    "description": "The URL of a planet resource, a planet that this person was born on or inhabits.",
                    "type": "string"
                },
                {
                    "name": "films",
                    "description": "An array of film resource URLs that this person has been in.",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "species",
                    "description": "An array of species resource URLs that this person belongs to.",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "starships",
                    "description": "An array of starship resource URLs that this person has piloted.",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "vehicles",
                    "description": "An array of vehicle resource URLs that this person has piloted.",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "url",
                    "description": "The hypermedia URL of this resource.",
                    "type": "string"
                },
                {
                    "name": "created",
                    "description": "The ISO 8601 date format of the time that this resource was created.",
                    "type": "string",
                    "format": "date-time"
                },
                {
                    "name": "edited",
                    "description": "The ISO 8601 date format of the time that this resource was edited. Search Fields:",
                    "type": "string",
                    "format": "date-time"
                }
            ]
        },
        {
            "name": "People_Collection",
            "type": "struct",
            "description": "",
            "parent": "Collection",
            "properties": [
                {
                    "name": "results",
                    "description": "",
                    "type": "array",
                    "reference": "People"
                }
            ]
        },
        {
            "name": "Planet",
            "type": "struct",
            "description": "A Planet is a large mass, planet or planetoid in the Star Wars Universe, at the time of 0 ABY",
            "properties": [
                {
                    "name": "name",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "diameter",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "rotation_period",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "orbital_period",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "gravity",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "population",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "climate",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "terrain",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "surface_water",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "residents",
                    "description": "",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "films",
                    "description": "",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "url",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "created",
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                },
                {
                    "name": "edited",
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                }
            ]
        },
        {
            "name": "Planet_Collection",
            "type": "struct",
            "description": "",
            "parent": "Collection",
            "properties": [
                {
                    "name": "results",
                    "description": "",
                    "type": "array",
                    "reference": "Planet"
                }
            ]
        },
        {
            "name": "Root",
            "type": "struct",
            "description": "",
            "properties": [
                {
                    "name": "films",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "people",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "planets",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "species",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "starships",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "vehicles",
                    "description": "",
                    "type": "string"
                }
            ]
        },
        {
            "name": "Species",
            "type": "struct",
            "description": "A Species is a type of person or character within the Star Wars Universe",
            "properties": [
                {
                    "name": "name",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "classification",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "designation",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "average_height",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "average_lifespan",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "eye_colors",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "hair_colors",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "skin_colors",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "language",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "homeworld",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "people",
                    "description": "",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "films",
                    "description": "",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "url",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "created",
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                },
                {
                    "name": "edited",
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                }
            ]
        },
        {
            "name": "Species_Collection",
            "type": "struct",
            "description": "",
            "parent": "Collection",
            "properties": [
                {
                    "name": "results",
                    "description": "",
                    "type": "array",
                    "reference": "Species"
                }
            ]
        },
        {
            "name": "Starship",
            "type": "struct",
            "description": "A Starship is a single transport craft that has hyperdrive capability",
            "properties": [
                {
                    "name": "name",
                    "description": "The name of this starship. The common name, such as \"Death Star\"",
                    "type": "string"
                },
                {
                    "name": "model",
                    "description": "The model or official name of this starship. Such as \"T-65 X-wing\" or \"DS-1 Orbital Battle Station\"",
                    "type": "string"
                },
                {
                    "name": "starship_class",
                    "description": "The class of this starship, such as \"Starfighter\" or \"Deep Space Mobile Battlestation\"",
                    "type": "string"
                },
                {
                    "name": "manufacturer",
                    "description": "The manufacturer of this starship. Comma separated if more than one",
                    "type": "string"
                },
                {
                    "name": "cost_in_credits",
                    "description": "The cost of this starship new, in galactic credits",
                    "type": "string"
                },
                {
                    "name": "length",
                    "description": "The length of this starship in meters",
                    "type": "string"
                },
                {
                    "name": "crew",
                    "description": "The number of personnel needed to run or pilot this starship",
                    "type": "string"
                },
                {
                    "name": "passengers",
                    "description": "The number of non-essential people this starship can transport",
                    "type": "string"
                },
                {
                    "name": "max_atmosphering_speed",
                    "description": "The maximum speed of this starship in the atmosphere. \"N\/A\" if this starship is incapable of atmospheric flight",
                    "type": "string"
                },
                {
                    "name": "hyperdrive_rating",
                    "description": "The class of this starships hyperdrive",
                    "type": "string"
                },
                {
                    "name": "MGLT",
                    "description": "The Maximum number of Megalights this starship can travel in a standard hour. A \"Megalight\" is a standard unit of distance and has never been defined before within the Star Wars universe. This figure is only really useful for measuring the difference in speed of starships. We can assume it is similar to AU, the distance between our Sun (Sol) and Earth",
                    "type": "string"
                },
                {
                    "name": "cargo_capacity",
                    "description": "The maximum number of kilograms that this starship can transport",
                    "type": "string"
                },
                {
                    "name": "consumables",
                    "description": "The maximum length of time that this starship can provide consumables for its entire crew without having to resupply",
                    "type": "string"
                },
                {
                    "name": "films",
                    "description": "An array of Film URL Resources that this starship has appeared in",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "pilots",
                    "description": "An array of People URL Resources that this starship has been piloted by",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "url",
                    "description": "The hypermedia URL of this resource",
                    "type": "string"
                },
                {
                    "name": "created",
                    "description": "The ISO 8601 date format of the time that this resource was created",
                    "type": "string",
                    "format": "date-time"
                },
                {
                    "name": "edited",
                    "description": "The ISO 8601 date format of the time that this resource was edited",
                    "type": "string",
                    "format": "date-time"
                }
            ]
        },
        {
            "name": "Starship_Collection",
            "type": "struct",
            "description": "",
            "parent": "Collection",
            "properties": [
                {
                    "name": "results",
                    "description": "",
                    "type": "array",
                    "reference": "Starship"
                }
            ]
        },
        {
            "name": "Vehicle",
            "type": "struct",
            "description": "A Vehicle is a single transport craft that does not have hyperdrive capability",
            "properties": [
                {
                    "name": "name",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "model",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "vehicle_class",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "manufacturer",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "length",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "cost_in_credits",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "crew",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "passengers",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "max_atmosphering_speed",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "cargo_capacity",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "consumables",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "films",
                    "description": "",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "pilots",
                    "description": "",
                    "type": "array",
                    "reference": "string"
                },
                {
                    "name": "url",
                    "description": "",
                    "type": "string"
                },
                {
                    "name": "created",
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                },
                {
                    "name": "edited",
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                }
            ]
        },
        {
            "name": "Vehicle_Collection",
            "type": "struct",
            "description": "",
            "parent": "Collection",
            "properties": [
                {
                    "name": "results",
                    "description": "",
                    "type": "array",
                    "reference": "Vehicle"
                }
            ]
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/connection/StarwarsSDK/sdk', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/connection/StarwarsSDK/sdk', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/connection/StarwarsSDK/sdk', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    protected function isTransactional(): bool
    {
        return false;
    }
}

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
    "operations": {
        "people.getAll": {
            "path": "\/people",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "People_Collection"
                }
            },
            "arguments": {
                "search": {
                    "in": "query",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get all the people",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "people.get": {
            "path": "\/people\/:id",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "People"
                }
            },
            "arguments": {
                "id": {
                    "in": "path",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get a specific people",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "film.getAll": {
            "path": "\/films",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "Film_Collection"
                }
            },
            "arguments": {
                "search": {
                    "in": "query",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get all the films",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "film.get": {
            "path": "\/films\/:id",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "Film"
                }
            },
            "arguments": {
                "id": {
                    "in": "path",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get a specific film",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "starship.getAll": {
            "path": "\/starships",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "Starship_Collection"
                }
            },
            "arguments": {
                "search": {
                    "in": "query",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get all the starships",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "starship.get": {
            "path": "\/starships\/:id",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "Starship"
                }
            },
            "arguments": {
                "id": {
                    "in": "path",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get a specific starship",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "species.getAll": {
            "path": "\/species",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "Species_Collection"
                }
            },
            "arguments": {
                "search": {
                    "in": "query",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get all the species",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "species.get": {
            "path": "\/species\/:id",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "Species"
                }
            },
            "arguments": {
                "id": {
                    "in": "path",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get a specific species",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "vehicle.getAll": {
            "path": "\/vehicles",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "Vehicle_Collection"
                }
            },
            "arguments": {
                "search": {
                    "in": "query",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get all the vehicles",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "vehicle.get": {
            "path": "\/vehicles\/:id",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "Vehicle"
                }
            },
            "arguments": {
                "id": {
                    "in": "path",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get a specific vehicle",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "planet.getAll": {
            "path": "\/planets",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "Planet_Collection"
                }
            },
            "arguments": {
                "search": {
                    "in": "query",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get all the planets",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        },
        "planet.get": {
            "path": "\/planets\/:id",
            "method": "GET",
            "return": {
                "code": 200,
                "schema": {
                    "type": "reference",
                    "target": "Planet"
                }
            },
            "arguments": {
                "id": {
                    "in": "path",
                    "schema": {
                        "type": "string"
                    }
                }
            },
            "throws": [],
            "description": "Get a specific planet",
            "stability": 1,
            "security": [],
            "authorization": true,
            "tags": []
        }
    },
    "definitions": {
        "Collection": {
            "description": "",
            "type": "struct",
            "properties": {
                "count": {
                    "description": "",
                    "type": "integer"
                },
                "next": {
                    "description": "",
                    "type": "string"
                },
                "previous": {
                    "description": "",
                    "type": "string"
                }
            }
        },
        "Film": {
            "description": "A Film is a single film",
            "type": "struct",
            "properties": {
                "title": {
                    "description": "The title of this film",
                    "type": "string"
                },
                "episode_id": {
                    "description": "The episode number of this film",
                    "type": "integer"
                },
                "opening_crawl": {
                    "description": "The opening paragraphs at the beginning of this film",
                    "type": "string"
                },
                "director": {
                    "description": "The name of the director of this film",
                    "type": "string"
                },
                "producer": {
                    "description": "The name(s) of the producer(s) of this film. Comma separated",
                    "type": "string"
                },
                "release_date": {
                    "description": "The ISO 8601 date format of film release at original creator country",
                    "type": "string",
                    "format": "date"
                },
                "species": {
                    "description": "An array of species resource URLs that are in this film",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "starships": {
                    "description": "An array of starship resource URLs that are in this film",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "vehicles": {
                    "description": "An array of vehicle resource URLs that are in this film",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "characters": {
                    "description": "An array of people resource URLs that are in this film",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "planets": {
                    "description": "An array of planet resource URLs that are in this film",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "url": {
                    "description": "The hypermedia URL of this resource",
                    "type": "string"
                },
                "created": {
                    "description": "The ISO 8601 date format of the time that this resource was created",
                    "type": "string",
                    "format": "date-time"
                },
                "edited": {
                    "description": "The ISO 8601 date format of the time that this resource was edited",
                    "type": "string",
                    "format": "date-time"
                }
            }
        },
        "Film_Collection": {
            "description": "",
            "type": "struct",
            "parent": {
                "type": "reference",
                "target": "Collection"
            },
            "properties": {
                "results": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "reference",
                        "target": "Film"
                    }
                }
            }
        },
        "People": {
            "description": "A People is an individual person or character within the Star Wars universe",
            "type": "struct",
            "properties": {
                "name": {
                    "description": "The name of this person.",
                    "type": "string"
                },
                "birth_year": {
                    "description": "The birth year of the person, using the in-universe standard of BBY or ABY - Before the Battle of Yavin or After the Battle of Yavin. The Battle of Yavin is a battle that occurs at the end of Star Wars episode IV: A New Hope.",
                    "type": "string"
                },
                "eye_color": {
                    "description": "The eye color of this person. Will be \"unknown\" if not known or \"n\/a\" if the person does not have an eye.",
                    "type": "string"
                },
                "gender": {
                    "description": "The gender of this person. Either \"Male\", \"Female\" or \"unknown\", \"n\/a\" if the person does not have a gender.",
                    "type": "string"
                },
                "hair_color": {
                    "description": "The hair color of this person. Will be \"unknown\" if not known or \"n\/a\" if the person does not have hair.",
                    "type": "string"
                },
                "height": {
                    "description": "The height of the person in centimeters.",
                    "type": "string"
                },
                "mass": {
                    "description": "The mass of the person in kilograms.",
                    "type": "string"
                },
                "skin_color": {
                    "description": "The skin color of this person.",
                    "type": "string"
                },
                "homeworld": {
                    "description": "The URL of a planet resource, a planet that this person was born on or inhabits.",
                    "type": "string"
                },
                "films": {
                    "description": "An array of film resource URLs that this person has been in.",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "species": {
                    "description": "An array of species resource URLs that this person belongs to.",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "starships": {
                    "description": "An array of starship resource URLs that this person has piloted.",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "vehicles": {
                    "description": "An array of vehicle resource URLs that this person has piloted.",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "url": {
                    "description": "The hypermedia URL of this resource.",
                    "type": "string"
                },
                "created": {
                    "description": "The ISO 8601 date format of the time that this resource was created.",
                    "type": "string",
                    "format": "date-time"
                },
                "edited": {
                    "description": "The ISO 8601 date format of the time that this resource was edited. Search Fields:",
                    "type": "string",
                    "format": "date-time"
                }
            }
        },
        "People_Collection": {
            "description": "",
            "type": "struct",
            "parent": {
                "type": "reference",
                "target": "Collection"
            },
            "properties": {
                "results": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "reference",
                        "target": "People"
                    }
                }
            }
        },
        "Planet": {
            "description": "A Planet is a large mass, planet or planetoid in the Star Wars Universe, at the time of 0 ABY",
            "type": "struct",
            "properties": {
                "name": {
                    "description": "",
                    "type": "string"
                },
                "diameter": {
                    "description": "",
                    "type": "string"
                },
                "rotation_period": {
                    "description": "",
                    "type": "string"
                },
                "orbital_period": {
                    "description": "",
                    "type": "string"
                },
                "gravity": {
                    "description": "",
                    "type": "string"
                },
                "population": {
                    "description": "",
                    "type": "string"
                },
                "climate": {
                    "description": "",
                    "type": "string"
                },
                "terrain": {
                    "description": "",
                    "type": "string"
                },
                "surface_water": {
                    "description": "",
                    "type": "string"
                },
                "residents": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "films": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "url": {
                    "description": "",
                    "type": "string"
                },
                "created": {
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                },
                "edited": {
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                }
            }
        },
        "Planet_Collection": {
            "description": "",
            "type": "struct",
            "parent": {
                "type": "reference",
                "target": "Collection"
            },
            "properties": {
                "results": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "reference",
                        "target": "Planet"
                    }
                }
            }
        },
        "Root": {
            "description": "",
            "type": "struct",
            "properties": {
                "films": {
                    "description": "",
                    "type": "string"
                },
                "people": {
                    "description": "",
                    "type": "string"
                },
                "planets": {
                    "description": "",
                    "type": "string"
                },
                "species": {
                    "description": "",
                    "type": "string"
                },
                "starships": {
                    "description": "",
                    "type": "string"
                },
                "vehicles": {
                    "description": "",
                    "type": "string"
                }
            }
        },
        "Species": {
            "description": "A Species is a type of person or character within the Star Wars Universe",
            "type": "struct",
            "properties": {
                "name": {
                    "description": "",
                    "type": "string"
                },
                "classification": {
                    "description": "",
                    "type": "string"
                },
                "designation": {
                    "description": "",
                    "type": "string"
                },
                "average_height": {
                    "description": "",
                    "type": "string"
                },
                "average_lifespan": {
                    "description": "",
                    "type": "string"
                },
                "eye_colors": {
                    "description": "",
                    "type": "string"
                },
                "hair_colors": {
                    "description": "",
                    "type": "string"
                },
                "skin_colors": {
                    "description": "",
                    "type": "string"
                },
                "language": {
                    "description": "",
                    "type": "string"
                },
                "homeworld": {
                    "description": "",
                    "type": "string"
                },
                "people": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "films": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "url": {
                    "description": "",
                    "type": "string"
                },
                "created": {
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                },
                "edited": {
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                }
            }
        },
        "Species_Collection": {
            "description": "",
            "type": "struct",
            "parent": {
                "type": "reference",
                "target": "Collection"
            },
            "properties": {
                "results": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "reference",
                        "target": "Species"
                    }
                }
            }
        },
        "Starship": {
            "description": "A Starship is a single transport craft that has hyperdrive capability",
            "type": "struct",
            "properties": {
                "name": {
                    "description": "The name of this starship. The common name, such as \"Death Star\"",
                    "type": "string"
                },
                "model": {
                    "description": "The model or official name of this starship. Such as \"T-65 X-wing\" or \"DS-1 Orbital Battle Station\"",
                    "type": "string"
                },
                "starship_class": {
                    "description": "The class of this starship, such as \"Starfighter\" or \"Deep Space Mobile Battlestation\"",
                    "type": "string"
                },
                "manufacturer": {
                    "description": "The manufacturer of this starship. Comma separated if more than one",
                    "type": "string"
                },
                "cost_in_credits": {
                    "description": "The cost of this starship new, in galactic credits",
                    "type": "string"
                },
                "length": {
                    "description": "The length of this starship in meters",
                    "type": "string"
                },
                "crew": {
                    "description": "The number of personnel needed to run or pilot this starship",
                    "type": "string"
                },
                "passengers": {
                    "description": "The number of non-essential people this starship can transport",
                    "type": "string"
                },
                "max_atmosphering_speed": {
                    "description": "The maximum speed of this starship in the atmosphere. \"N\/A\" if this starship is incapable of atmospheric flight",
                    "type": "string"
                },
                "hyperdrive_rating": {
                    "description": "The class of this starships hyperdrive",
                    "type": "string"
                },
                "MGLT": {
                    "description": "The Maximum number of Megalights this starship can travel in a standard hour. A \"Megalight\" is a standard unit of distance and has never been defined before within the Star Wars universe. This figure is only really useful for measuring the difference in speed of starships. We can assume it is similar to AU, the distance between our Sun (Sol) and Earth",
                    "type": "string"
                },
                "cargo_capacity": {
                    "description": "The maximum number of kilograms that this starship can transport",
                    "type": "string"
                },
                "consumables": {
                    "description": "The maximum length of time that this starship can provide consumables for its entire crew without having to resupply",
                    "type": "string"
                },
                "films": {
                    "description": "An array of Film URL Resources that this starship has appeared in",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "pilots": {
                    "description": "An array of People URL Resources that this starship has been piloted by",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "url": {
                    "description": "The hypermedia URL of this resource",
                    "type": "string"
                },
                "created": {
                    "description": "The ISO 8601 date format of the time that this resource was created",
                    "type": "string",
                    "format": "date-time"
                },
                "edited": {
                    "description": "The ISO 8601 date format of the time that this resource was edited",
                    "type": "string",
                    "format": "date-time"
                }
            }
        },
        "Starship_Collection": {
            "description": "",
            "type": "struct",
            "parent": {
                "type": "reference",
                "target": "Collection"
            },
            "properties": {
                "results": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "reference",
                        "target": "Starship"
                    }
                }
            }
        },
        "Vehicle": {
            "description": "A Vehicle is a single transport craft that does not have hyperdrive capability",
            "type": "struct",
            "properties": {
                "name": {
                    "description": "",
                    "type": "string"
                },
                "model": {
                    "description": "",
                    "type": "string"
                },
                "vehicle_class": {
                    "description": "",
                    "type": "string"
                },
                "manufacturer": {
                    "description": "",
                    "type": "string"
                },
                "length": {
                    "description": "",
                    "type": "string"
                },
                "cost_in_credits": {
                    "description": "",
                    "type": "string"
                },
                "crew": {
                    "description": "",
                    "type": "string"
                },
                "passengers": {
                    "description": "",
                    "type": "string"
                },
                "max_atmosphering_speed": {
                    "description": "",
                    "type": "string"
                },
                "cargo_capacity": {
                    "description": "",
                    "type": "string"
                },
                "consumables": {
                    "description": "",
                    "type": "string"
                },
                "films": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "pilots": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "string"
                    }
                },
                "url": {
                    "description": "",
                    "type": "string"
                },
                "created": {
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                },
                "edited": {
                    "description": "",
                    "type": "string",
                    "format": "date-time"
                }
            }
        },
        "Vehicle_Collection": {
            "description": "",
            "type": "struct",
            "parent": {
                "type": "reference",
                "target": "Collection"
            },
            "properties": {
                "results": {
                    "description": "",
                    "type": "array",
                    "schema": {
                        "type": "reference",
                        "target": "Vehicle"
                    }
                }
            }
        }
    }
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

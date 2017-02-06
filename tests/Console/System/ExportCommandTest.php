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

namespace Fusio\Impl\Tests\Console\System;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ExportCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ExportCommandTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService('console')->find('system:export');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $actual = $commandTester->getDisplay();
        $actual = preg_replace('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/m', '[datetime]', $actual);

        $expect = <<<'JSON'
{
    "actionClass": [
        "Fusio\\Adapter\\Sql\\Action\\SqlTable",
        "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
        "Fusio\\Adapter\\V8\\Action\\V8Processor"
    ],
    "connectionClass": [
        "Fusio\\Adapter\\Http\\Connection\\Http",
        "Fusio\\Adapter\\Sql\\Connection\\Sql",
        "Fusio\\Adapter\\Sql\\Connection\\SqlAdvanced"
    ],
    "connection": [
        {
            "status": 1,
            "name": "Native",
            "class": "Fusio\\Impl\\Tests\\Connection\\Native",
            "config": {}
        }
    ],
    "schema": [
        {
            "name": "Foo-Schema",
            "source": {
                "id": "http:\/\/phpsx.org#",
                "title": "test",
                "type": "object",
                "properties": {
                    "totalResults": {
                        "type": "integer"
                    },
                    "itemsPerPage": {
                        "type": "integer"
                    },
                    "startIndex": {
                        "type": "integer"
                    },
                    "entry": {
                        "type": "array",
                        "items": {
                            "type": "object",
                            "properties": {
                                "id": {
                                    "type": "integer"
                                },
                                "title": {
                                    "type": "string"
                                },
                                "content": {
                                    "type": "string"
                                },
                                "date": {
                                    "type": "string",
                                    "format": "date-time"
                                }
                            }
                        }
                    }
                }
            }
        },
        {
            "name": "Passthru",
            "source": {
                "id": "http:\/\/fusio-project.org",
                "title": "passthru",
                "type": "object",
                "description": "No schema was specified all data will pass through. Please contact the API provider for more informations about the data format.",
                "properties": []
            }
        }
    ],
    "action": [
        {
            "name": "Sql-Table",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlTable",
            "config": {
                "connection": "Native",
                "table": "app_news"
            },
            "date": "[datetime]"
        },
        {
            "name": "Util-Static-Response",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "config": {
                "response": "{\"foo\": \"bar\"}"
            },
            "date": "[datetime]"
        },
        {
            "name": "Welcome",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "config": {
                "response": "{\n    \"message\": \"Congratulations the installation of Fusio was successful\",\n    \"links\": [\n        {\n            \"rel\": \"about\",\n            \"name\": \"http:\\\/\\\/fusio-project.org\"\n        }\n    ]\n}"
            },
            "date": "[datetime]"
        }
    ],
    "routes": [
        {
            "status": 1,
            "path": "\/foo",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "response": "Foo-Schema",
                            "action": "Sql-Table"
                        },
                        "POST": {
                            "active": true,
                            "public": false,
                            "request": "Passthru",
                            "response": "Passthru",
                            "action": "Sql-Table"
                        }
                    }
                }
            ]
        },
        {
            "status": 1,
            "path": "\/",
            "controller": "Fusio\\Impl\\Controller\\SchemaApiController",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "response": "Passthru",
                            "action": "Welcome"
                        }
                    }
                }
            ]
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}

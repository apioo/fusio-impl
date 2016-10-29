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
        $expect = <<<'JSON'
{
    "actionClass": [
        "Fusio\\Adapter\\Http\\Action\\HttpProxy",
        "Fusio\\Adapter\\Http\\Action\\HttpRequest",
        "Fusio\\Adapter\\Sql\\Action\\SqlBuilder",
        "Fusio\\Adapter\\Sql\\Action\\SqlExecute",
        "Fusio\\Adapter\\Sql\\Action\\SqlFetchAll",
        "Fusio\\Adapter\\Sql\\Action\\SqlFetchRow",
        "Fusio\\Adapter\\Sql\\Action\\SqlTable",
        "Fusio\\Adapter\\Util\\Action\\UtilCache",
        "Fusio\\Adapter\\Util\\Action\\UtilComposite",
        "Fusio\\Adapter\\Util\\Action\\UtilCondition",
        "Fusio\\Adapter\\Util\\Action\\UtilPipe",
        "Fusio\\Adapter\\Util\\Action\\UtilProcessor",
        "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
        "Fusio\\Adapter\\Util\\Action\\UtilTransform",
        "Fusio\\Adapter\\Util\\Action\\UtilTryCatch",
        "Fusio\\Adapter\\Util\\Action\\UtilValidator"
    ],
    "connectionClass": [
        "Fusio\\Adapter\\Sql\\Connection\\DBAL",
        "Fusio\\Adapter\\Sql\\Connection\\DBALAdvanced",
        "Fusio\\Adapter\\Util\\Connection\\Native"
    ],
    "connection": [
        {
            "name": "DBAL",
            "class": "Fusio\\Adapter\\Sql\\Connection\\DBAL",
            "config": {
                "type": "pdo_mysql",
                "host": "127.0.0.1",
                "username": "root",
                "database": "bar"
            }
        },
        {
            "name": "Native-Connection",
            "class": "Fusio\\Adapter\\Util\\Connection\\Native",
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
        },
        {
            "name": "Passthru",
            "source": {
                "id": "http:\/\/fusio-project.org",
                "title": "passthru",
                "type": "object",
                "description": "No schema was specified all data will pass thru. Please contact the API provider for more informations about the data format.",
                "properties": []
            }
        }
    ],
    "action": [
        {
            "name": "Sql-Fetch-Row",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlFetchRow",
            "config": {
                "connection": "Native-Connection",
                "sql": "SELECT * FROM app_news"
            }
        },
        {
            "name": "Sql-Fetch-All",
            "class": "Fusio\\Adapter\\Sql\\Action\\SqlFetchAll",
            "config": {
                "connection": "Native-Connection",
                "sql": "SELECT * FROM app_news"
            }
        },
        {
            "name": "Welcome",
            "class": "Fusio\\Adapter\\Util\\Action\\UtilStaticResponse",
            "config": {
                "response": "{\n    \"message\": \"Congratulations the installation of Fusio was successful\",\n    \"links\": [\n        {\n            \"rel\": \"about\",\n            \"name\": \"http:\\\/\\\/fusio-project.org\"\n        }\n    ]\n}"
            }
        }
    ],
    "routes": [
        {
            "path": "\/foo",
            "config": [
                {
                    "version": 1,
                    "status": 4,
                    "methods": {
                        "GET": {
                            "active": true,
                            "public": true,
                            "response": "Foo-Schema",
                            "action": "Sql-Fetch-Row"
                        },
                        "POST": {
                            "active": true,
                            "public": false,
                            "request": "Foo-Schema",
                            "response": "Passthru",
                            "action": "Sql-Fetch-Row"
                        }
                    }
                }
            ]
        },
        {
            "path": "\/",
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

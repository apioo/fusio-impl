<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Console;

use Fusio\Impl\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * SystemExportCommandTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SystemExportCommandTest extends ControllerDbTestCase
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
        "Fusio\\Impl\\Action\\CacheResponse",
        "Fusio\\Impl\\Action\\Composite",
        "Fusio\\Impl\\Action\\Condition",
        "Fusio\\Impl\\Action\\HttpProxy",
        "Fusio\\Impl\\Action\\HttpRequest",
        "Fusio\\Impl\\Action\\MongoDelete",
        "Fusio\\Impl\\Action\\MongoFetchAll",
        "Fusio\\Impl\\Action\\MongoFetchRow",
        "Fusio\\Impl\\Action\\MongoInsert",
        "Fusio\\Impl\\Action\\MongoUpdate",
        "Fusio\\Impl\\Action\\MqAmqp",
        "Fusio\\Impl\\Action\\MqBeanstalk",
        "Fusio\\Impl\\Action\\Pipe",
        "Fusio\\Impl\\Action\\Processor",
        "Fusio\\Impl\\Action\\SqlExecute",
        "Fusio\\Impl\\Action\\SqlFetchAll",
        "Fusio\\Impl\\Action\\SqlFetchRow",
        "Fusio\\Impl\\Action\\StaticResponse",
        "Fusio\\Impl\\Action\\Transform",
        "Fusio\\Impl\\Action\\Validator"
    ],
    "connectionClass": [
        "Fusio\\Impl\\Connection\\Beanstalk",
        "Fusio\\Impl\\Connection\\DBAL",
        "Fusio\\Impl\\Connection\\DBALAdvanced",
        "Fusio\\Impl\\Connection\\MongoDB",
        "Fusio\\Impl\\Connection\\Native",
        "Fusio\\Impl\\Connection\\RabbitMQ"
    ],
    "connection": [
        {
            "name": "MongoDB",
            "class": "Fusio\\Impl\\Connection\\MongoDB",
            "config": {
                "url": "mongodb:\/\/localhost:27017",
                "database": "bar"
            }
        },
        {
            "name": "DBAL",
            "class": "Fusio\\Impl\\Connection\\DBAL",
            "config": {
                "type": "pdo_mysql",
                "host": "127.0.0.1",
                "username": "root",
                "database": "bar"
            }
        },
        {
            "name": "Native-Connection",
            "class": "Fusio\\Impl\\Connection\\Native",
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
            "class": "Fusio\\Impl\\Action\\SqlFetchRow",
            "config": {
                "connection": "Native-Connection",
                "sql": "SELECT * FROM app_news"
            }
        },
        {
            "name": "Sql-Fetch-All",
            "class": "Fusio\\Impl\\Action\\SqlFetchAll",
            "config": {
                "connection": "Native-Connection",
                "sql": "SELECT * FROM app_news"
            }
        },
        {
            "name": "Welcome",
            "class": "Fusio\\Impl\\Action\\StaticResponse",
            "config": {
                "response": "{\n    \"message\": \"Congratulations the installation of Fusio was successful\",\n    \"links\": [\n        {\n            \"rel\": \"about\",\n            \"name\": \"http:\\\/\\\/fusio-project.org\"\n        }\n    ]\n}"
            }
        }
    ],
    "routes": [
        {
            "methods": "GET|POST|PUT|DELETE",
            "path": "\/foo",
            "config": [
                {
                    "active": true,
                    "status": 4,
                    "name": "1",
                    "methods": [
                        {
                            "name": "GET",
                            "action": "Sql-Fetch-Row",
                            "response": "Foo-Schema"
                        },
                        {
                            "active": true,
                            "public": false,
                            "name": "POST",
                            "action": "Sql-Fetch-Row",
                            "request": "Foo-Schema",
                            "response": "Passthru"
                        },
                        {
                            "name": "PUT"
                        },
                        {
                            "name": "DELETE"
                        }
                    ]
                }
            ]
        },
        {
            "methods": "GET|POST|PUT|DELETE",
            "path": "\/",
            "config": [
                {
                    "active": true,
                    "status": 4,
                    "name": "1",
                    "methods": [
                        {
                            "active": true,
                            "public": true,
                            "name": "GET",
                            "action": "Welcome",
                            "response": "Passthru"
                        }
                    ]
                }
            ]
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}

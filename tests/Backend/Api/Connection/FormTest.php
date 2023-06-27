<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\Api\Connection;

use Fusio\Adapter\Sql\Connection\Sql;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * FormTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class FormTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/connection/form?class=' . urlencode(Sql::class), 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "element": [
        {
            "element": "select",
            "name": "type",
            "title": "Type",
            "help": "The driver which is used to connect to the database",
            "options": [
                {
                    "key": "pdo_mysql",
                    "value": "MySQL"
                },
                {
                    "key": "pdo_pgsql",
                    "value": "PostgreSQL"
                },
                {
                    "key": "sqlsrv",
                    "value": "Microsoft SQL Server"
                },
                {
                    "key": "oci8",
                    "value": "Oracle Database"
                },
                {
                    "key": "sqlanywhere",
                    "value": "SAP Sybase SQL Anywhere"
                }
            ]
        },
        {
            "element": "input",
            "name": "host",
            "title": "Host",
            "help": "The IP or hostname of the database server",
            "type": "text"
        },
        {
            "element": "input",
            "name": "username",
            "title": "Username",
            "help": "The name of the database user",
            "type": "text"
        },
        {
            "element": "input",
            "name": "password",
            "title": "Password",
            "help": "The password of the database user",
            "type": "password"
        },
        {
            "element": "input",
            "name": "database",
            "title": "Database",
            "help": "The name of the database which is used upon connection",
            "type": "text"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetClassDotNotation()
    {
        $response = $this->sendRequest('/backend/connection/form?class=' . str_replace('\\', '.', Sql::class), 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "element": [
        {
            "element": "select",
            "name": "type",
            "title": "Type",
            "help": "The driver which is used to connect to the database",
            "options": [
                {
                    "key": "pdo_mysql",
                    "value": "MySQL"
                },
                {
                    "key": "pdo_pgsql",
                    "value": "PostgreSQL"
                },
                {
                    "key": "sqlsrv",
                    "value": "Microsoft SQL Server"
                },
                {
                    "key": "oci8",
                    "value": "Oracle Database"
                },
                {
                    "key": "sqlanywhere",
                    "value": "SAP Sybase SQL Anywhere"
                }
            ]
        },
        {
            "element": "input",
            "name": "host",
            "title": "Host",
            "help": "The IP or hostname of the database server",
            "type": "text"
        },
        {
            "element": "input",
            "name": "username",
            "title": "Username",
            "help": "The name of the database user",
            "type": "text"
        },
        {
            "element": "input",
            "name": "password",
            "title": "Password",
            "help": "The password of the database user",
            "type": "password"
        },
        {
            "element": "input",
            "name": "database",
            "title": "Database",
            "help": "The name of the database which is used upon connection",
            "type": "text"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

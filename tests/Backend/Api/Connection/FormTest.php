<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\Api\Connection;

use Fusio\Adapter\Sql\Connection\Sql;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * FormTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class FormTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/connection/form', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/form.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
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
}

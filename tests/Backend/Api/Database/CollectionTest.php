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

namespace Fusio\Impl\Tests\Backend\Api\Database;

use Doctrine\DBAL\Schema\Schema;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/database/1', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "entry": [
        {
            "name": "app_news"
        },
        {
            "name": "fusio_action"
        },
        {
            "name": "fusio_action_class"
        },
        {
            "name": "fusio_app"
        },
        {
            "name": "fusio_app_code"
        },
        {
            "name": "fusio_app_scope"
        },
        {
            "name": "fusio_app_token"
        },
        {
            "name": "fusio_config"
        },
        {
            "name": "fusio_connection"
        },
        {
            "name": "fusio_connection_class"
        },
        {
            "name": "fusio_log"
        },
        {
            "name": "fusio_log_error"
        },
        {
            "name": "fusio_meta"
        },
        {
            "name": "fusio_routes"
        },
        {
            "name": "fusio_routes_action"
        },
        {
            "name": "fusio_routes_method"
        },
        {
            "name": "fusio_routes_schema"
        },
        {
            "name": "fusio_schema"
        },
        {
            "name": "fusio_scope"
        },
        {
            "name": "fusio_scope_routes"
        },
        {
            "name": "fusio_user"
        },
        {
            "name": "fusio_user_grant"
        },
        {
            "name": "fusio_user_scope"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/database/1', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'   => 'foo_table',
            'columns'  => [[
                'name' => 'id',
                'type' => 'integer',
            ],[
                'name' => 'name',
                'type' => 'string',
            ]],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Table successful created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        /** @var Schema $schema */
        $schema = Environment::getService('connection')->getSchemaManager()->createSchema();
        $table  = $schema->getTable('foo_table');

        $this->assertEquals(2, count($table->getColumns()));
        $this->assertEquals('id', $table->getColumn('id')->getName());
        $this->assertEquals('integer', $table->getColumn('id')->getType()->getName());
        $this->assertEquals('name', $table->getColumn('name')->getName());
        $this->assertEquals('string', $table->getColumn('name')->getType()->getName());
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/database/1', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/database/1', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

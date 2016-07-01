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
 * EntityTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/database/1/app_news', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "name": "app_news",
    "columns": [
        {
            "name": "id",
            "type": "integer",
            "null": false
        },
        {
            "name": "title",
            "type": "string",
            "length": 64,
            "null": false
        },
        {
            "name": "content",
            "type": "string",
            "length": 255,
            "null": false
        },
        {
            "name": "date",
            "type": "datetime",
            "null": false
        }
    ],
    "indexes": [
        {
            "name": "PRIMARY",
            "columns": [
                "id"
            ],
            "primary": true,
            "unique": true
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/database/1/app_news', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/database/1/app_news', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'    => 'app_news',
            'columns' => [[
                'name'   => 'id',
                'type'   => 'integer',
                'null'   => false,
            ],[
                'name'   => 'title',
                'type'   => 'string',
                'length' => 64,
                'null'   => false,
            ],[
                'name'   => 'content',
                'type'   => 'string',
                'length' => 240,
                'null'   => false,
            ],[
                'name'   => 'date',
                'type'   => 'datetime',
                'null'   => false,
            ]],
            'indexes' => [[
                'name'    => 'PRIMARY',
                'columns' => ['id'],
                'primary' => true,
                'unique'  => true,
            ]]
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Table successful updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        /** @var Schema $schema */
        $schema = Environment::getService('connection')->getSchemaManager()->createSchema();
        $table  = $schema->getTable('app_news');

        $this->assertEquals(4, count($table->getColumns()));
        $this->assertEquals('id', $table->getColumn('id')->getName());
        $this->assertEquals('integer', $table->getColumn('id')->getType()->getName());
        $this->assertEquals('title', $table->getColumn('title')->getName());
        $this->assertEquals('string', $table->getColumn('title')->getType()->getName());
        $this->assertEquals(64, $table->getColumn('title')->getLength());
        $this->assertEquals('content', $table->getColumn('content')->getName());
        $this->assertEquals('string', $table->getColumn('content')->getType()->getName());
        $this->assertEquals(240, $table->getColumn('content')->getLength());
        $this->assertEquals('date', $table->getColumn('date')->getName());
        $this->assertEquals('datetime', $table->getColumn('date')->getType()->getName());
    }

    /**
     * @expectedException \Doctrine\DBAL\Schema\SchemaException
     */
    public function testDelete()
    {
        /** @var Schema $toSchema */
        $connection = Environment::getService('connection');
        $toSchema = $connection->getSchemaManager()->createSchema();
        $table = $toSchema->createTable('foo');
        $table->addColumn('id', 'integer');
        $table->addColumn('name', 'string');

        /** @var Schema $fromSchema */
        $fromSchema = $connection->getSchemaManager()->createSchema();
        $queries = $fromSchema->getMigrateToSql($toSchema, $connection->getDatabasePlatform());
        foreach ($queries as $query) {
            $connection->query($query);
        }

        $response = $this->sendRequest('http://127.0.0.1/backend/database/1/foo', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Table successful deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        /** @var Schema $schema */
        $schema = Environment::getService('connection')->getSchemaManager()->createSchema();
        $schema->getTable('foo');
    }
}

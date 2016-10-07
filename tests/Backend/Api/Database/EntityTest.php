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

namespace Fusio\Impl\Tests\Backend\Api\Database;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Doctrine\DBAL\Schema\Schema;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    protected function setUp()
    {
        parent::setUp();

        /** @var Schema $toSchema */
        $connection = Environment::getService('connection');
        $toSchema   = $connection->getSchemaManager()->createSchema();

        $table = $toSchema->createTable('foo');
        $table->addColumn('id', 'integer');
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('date', 'datetime');
        $table->setPrimaryKey(['id']);

        /** @var Schema $fromSchema */
        $fromSchema = $connection->getSchemaManager()->createSchema();
        $queries    = $fromSchema->getMigrateToSql($toSchema, $connection->getDatabasePlatform());
        foreach ($queries as $query) {
            $connection->query($query);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();

        /** @var Schema $toSchema */
        $connection = Environment::getService('connection');
        $toSchema   = $connection->getSchemaManager()->createSchema();

        if ($toSchema->hasTable('foo')) {
            $toSchema->dropTable('foo');

            /** @var Schema $fromSchema */
            $fromSchema = $connection->getSchemaManager()->createSchema();
            $queries    = $fromSchema->getMigrateToSql($toSchema, $connection->getDatabasePlatform());
            foreach ($queries as $query) {
                $connection->query($query);
            }
        }
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
            "null": false,
            "autoincrement": true
        },
        {
            "name": "title",
            "type": "string",
            "length": 64,
            "null": false,
            "autoincrement": false
        },
        {
            "name": "content",
            "type": "string",
            "length": 255,
            "null": false,
            "autoincrement": false
        },
        {
            "name": "date",
            "type": "datetime",
            "null": false,
            "autoincrement": false
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
    ],
    "foreignKeys": []
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
        $response = $this->sendRequest('http://127.0.0.1/backend/database/1/foo', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'    => 'foo',
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
        $table  = $schema->getTable('foo');

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

    public function testPutPreview()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/database/1/foo?preview=1', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'    => 'foo',
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

        $body = (string) $response->getBody();

        $platform = Environment::getService('connection')->getDatabasePlatform();
        if ($platform instanceof MySqlPlatform) {
            $expect = <<<'JSON'
{
    "success": true,
    "message": "Table successful updated",
    "queries": [
        "ALTER TABLE foo ADD title VARCHAR(64) NOT NULL, ADD content VARCHAR(240) NOT NULL, DROP name"
    ]
}
JSON;
        } elseif ($platform instanceof SqlitePlatform) {
            $expect = <<<'JSON'
{
    "success": true,
    "message": "Table successful updated",
    "queries": [
        "CREATE TEMPORARY TABLE __temp__foo AS SELECT id, date FROM foo",
        "DROP TABLE foo",
        "CREATE TABLE foo (id INTEGER NOT NULL, date DATETIME NOT NULL, title VARCHAR(64) NOT NULL, content VARCHAR(240) NOT NULL, PRIMARY KEY(id))",
        "INSERT INTO foo (id, date) SELECT id, date FROM __temp__foo",
        "DROP TABLE __temp__foo"
    ]
}
JSON;
        } else {
            $this->fail('Invalid database platform');
        }

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    /**
     * @expectedException \Doctrine\DBAL\Schema\SchemaException
     */
    public function testDelete()
    {
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

    public function testDeletePreview()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/database/1/foo?preview=1', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Table successful deleted",
    "queries": [
        "DROP TABLE foo"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

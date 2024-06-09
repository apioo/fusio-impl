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

namespace Fusio\Impl\Tests\Backend\Api\Database\Table;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Type;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/database/Test/app_news', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        if ($this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform && PHP_MAJOR_VERSION === 8 && PHP_MINOR_VERSION === 3) {
            $length = 'null';
        } else {
            $length = 'null';
        }

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "name": "app_news",
    "columns": [
        {
            "name": "id",
            "type": "integer",
            "length": null,
            "precision": 10,
            "scale": 0,
            "unsigned": false,
            "fixed": false,
            "notNull": true,
            "default": null,
            "comment": null
        },
        {
            "name": "title",
            "type": "string",
            "length": 64,
            "precision": 10,
            "scale": 0,
            "unsigned": false,
            "fixed": false,
            "notNull": true,
            "default": null,
            "comment": null
        },
        {
            "name": "content",
            "type": "string",
            "length": 255,
            "precision": 10,
            "scale": 0,
            "unsigned": false,
            "fixed": false,
            "notNull": true,
            "default": null,
            "comment": null
        },
        {
            "name": "date",
            "type": "datetime",
            "length": {$length},
            "precision": 10,
            "scale": 0,
            "unsigned": false,
            "fixed": false,
            "notNull": true,
            "default": null,
            "comment": null
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        $response = $this->sendRequest('/backend/database/Test/foobar', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Provided table does not exist', $data->message);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/database/Test/app_news', 'POST', array(
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
        $this->sendRequest('/backend/database/Test', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'    => 'my_table_put',
            'columns' => [
                [
                    'name' => 'id',
                    'type' => 'integer',
                    'autoIncrement' => true,
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                ]
            ],
        ]));

        $response = $this->sendRequest('/backend/database/Test/my_table_put', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'    => 'app_news',
            'columns' => [
                [
                    'name' => 'id',
                    'type' => 'integer',
                    'autoIncrement' => true,
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                ],
                [
                    'name' => 'description',
                    'type' => 'string',
                ]
            ],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Table successfully updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $schemaManager = $this->connection->createSchemaManager();
        $table = $schemaManager->introspectTable('my_table_put');

        $this->assertEquals('my_table_put', $table->getName());

        $columns = $table->getColumns();

        $this->assertEquals(3, count($columns));
        $this->assertEquals('id', $columns['id']->getName());
        $this->assertEquals('integer', Type::lookupName($columns['id']->getType()));
        $this->assertEquals('title', $columns['title']->getName());
        $this->assertEquals('string', Type::lookupName($columns['title']->getType()));
        $this->assertEquals('description', $columns['description']->getName());
        $this->assertEquals('string', Type::lookupName($columns['description']->getType()));
    }

    public function testDelete()
    {
        $this->sendRequest('/backend/database/Test', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'    => 'my_table_delete',
            'columns' => [
                [
                    'name' => 'id',
                    'type' => 'integer',
                    'autoIncrement' => true,
                ],
                [
                    'name' => 'title',
                    'type' => 'string',
                ]
            ],
        ]));

        $response = $this->sendRequest('/backend/database/Test/my_table_delete', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Table successfully deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $schemaManager = $this->connection->createSchemaManager();

        $this->assertFalse($schemaManager->tablesExist('my_table_delete'));
    }
}

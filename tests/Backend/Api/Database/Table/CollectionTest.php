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

use Doctrine\DBAL\Types\Type;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/database/Test', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "tables": [
        "app_news",
        "doctrine_migration_versions",
        "fusio_action",
        "fusio_app",
        "fusio_app_code",
        "fusio_app_scope",
        "fusio_audit",
        "fusio_category",
        "fusio_config",
        "fusio_connection",
        "fusio_cronjob",
        "fusio_cronjob_error",
        "fusio_event",
        "fusio_identity",
        "fusio_identity_request",
        "fusio_log",
        "fusio_log_error",
        "fusio_operation",
        "fusio_page",
        "fusio_plan",
        "fusio_plan_scope",
        "fusio_plan_usage",
        "fusio_rate",
        "fusio_rate_allocation",
        "fusio_role",
        "fusio_role_scope",
        "fusio_schema",
        "fusio_scope",
        "fusio_scope_operation",
        "fusio_token",
        "fusio_transaction",
        "fusio_user",
        "fusio_user_grant",
        "fusio_user_scope",
        "fusio_webhook",
        "fusio_webhook_response",
        "messenger_messages"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $schemaManager = $this->connection->createSchemaManager();
        if ($schemaManager->tablesExist('my_table')) {
            $schemaManager->dropTable('my_table');
        }

        $response = $this->sendRequest('/backend/database/Test', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'    => 'my_table',
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
            'primaryKey' => 'id',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Table successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $table = $schemaManager->introspectTable('my_table');

        $this->assertEquals('my_table', $table->getName());

        $columns = $table->getColumns();

        $this->assertEquals(2, count($columns));
        $this->assertEquals('id', $columns['id']->getName());
        $this->assertEquals('integer', Type::lookupName($columns['id']->getType()));
        $this->assertEquals('title', $columns['title']->getName());
        $this->assertEquals('string', Type::lookupName($columns['title']->getType()));
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/database/Test', 'PUT', array(
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
        $response = $this->sendRequest('/backend/database/Test', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

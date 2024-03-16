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

namespace Fusio\Impl\Tests\Backend\Api\Role;

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
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = Fixture::getReference('fusio_role', 'Consumer')->resolve($this->connection);
    }

    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/role/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 3,
    "categoryId": 1,
    "status": 1,
    "name": "Consumer",
    "scopes": [
        "consumer",
        "consumer.account",
        "consumer.app",
        "consumer.event",
        "consumer.grant",
        "consumer.identity",
        "consumer.log",
        "consumer.page",
        "consumer.payment",
        "consumer.plan",
        "consumer.scope",
        "consumer.transaction",
        "consumer.webhook",
        "authorization",
        "default"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetByName()
    {
        $response = $this->sendRequest('/backend/role/~Consumer', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 3,
    "categoryId": 1,
    "status": 1,
    "name": "Consumer",
    "scopes": [
        "consumer",
        "consumer.account",
        "consumer.app",
        "consumer.event",
        "consumer.grant",
        "consumer.identity",
        "consumer.log",
        "consumer.page",
        "consumer.payment",
        "consumer.plan",
        "consumer.scope",
        "consumer.transaction",
        "consumer.webhook",
        "authorization",
        "default"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        $response = $this->sendRequest('/backend/role/10', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Could not find role', $data->message);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/role/' . $this->id, 'POST', array(
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
        $response = $this->sendRequest('/backend/role/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'categoryId' => 3,
            'name'       => 'foo',
            'scopes'     => ['backend.action'],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Role successfully updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'category_id', 'status', 'name')
            ->from('fusio_role')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => $this->id]);

        $this->assertEquals(3, $row['category_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('foo', $row['name']);

        $sql = $this->connection->createQueryBuilder()
            ->select('scope_id')
            ->from('fusio_role_scope')
            ->where('role_id = :role_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $result = $this->connection->fetchAllAssociative($sql, ['role_id' => $this->id]);

        $this->assertEquals(1, count($result));
        $this->assertEquals(6, $result[0]['scope_id']);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/role/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Role successfully deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_role')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => $this->id]);

        $this->assertEquals(0, $row['status']);
    }
}

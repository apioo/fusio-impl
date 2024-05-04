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

namespace Fusio\Impl\Tests\Backend\Api\User;

use Fusio\Impl\Table\User;
use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
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

        $this->id = Fixture::getReference('fusio_user', 'Consumer')->resolve($this->connection);
    }

    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/user/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "id": 2,
    "roleId": 3,
    "planId": 1,
    "status": 1,
    "name": "Consumer",
    "email": "consumer@localhost.com",
    "points": 100,
    "metadata": {
        "foo": "bar"
    },
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
        "consumer.token",
        "consumer.transaction",
        "consumer.webhook",
        "authorization",
        "foo",
        "bar"
    ],
    "plans": [
        {
            "id": 2,
            "name": "Plan B",
            "price": 49.99,
            "points": 1000
        }
    ],
    "apps": [
        {
            "id": 5,
            "status": 3,
            "name": "Deactivated",
            "url": "http:\/\/google.com",
            "appKey": "[uuid]",
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 2,
            "name": "Pending",
            "url": "http:\/\/google.com",
            "appKey": "[uuid]",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "status": 1,
            "name": "Foo-App",
            "url": "http:\/\/google.com",
            "appKey": "[uuid]",
            "date": "[datetime]"
        }
    ],
    "date": "[datetime]"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        $response = $this->sendRequest('/backend/user/10', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Could not find user', $data->message);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/user/' . $this->id, 'POST', array(
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
        $metadata = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/user/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'roleId'   => 2,
            'planId'   => 2,
            'status'   => User::STATUS_ACTIVE,
            'name'     => 'bar',
            'email'    => 'bar@bar.com',
            'scopes'   => ['bar'],
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "User successfully updated",
    "id": "2"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'role_id', 'plan_id', 'status', 'name', 'email', 'metadata')
            ->from('fusio_user')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(2, $row['role_id']);
        $this->assertEquals(2, $row['plan_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('bar', $row['name']);
        $this->assertEquals('bar@bar.com', $row['email']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);

        $sql = $this->connection->createQueryBuilder()
            ->select('user_id', 'scope_id')
            ->from('fusio_user_scope')
            ->where('user_id = :user_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $scopes = $this->connection->fetchAllAssociative($sql, ['user_id' => $this->id]);

        $this->assertEquals(1, count($scopes));
        $this->assertEquals(2, $scopes[0]['user_id']);
        $this->assertEquals(50, $scopes[0]['scope_id']);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/user/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "User successfully deleted",
    "id": "2"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_user')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(User::STATUS_DELETED, $row['status']);
    }
}

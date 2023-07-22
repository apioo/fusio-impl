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

use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
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
        $response = $this->sendRequest('/backend/user', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 4,
            "roleId": 2,
            "status": 1,
            "name": "Developer",
            "email": "developer@localhost.com",
            "points": 10,
            "date": "[datetime]"
        },
        {
            "id": 3,
            "roleId": 3,
            "status": 2,
            "name": "Disabled",
            "email": "disabled@localhost.com",
            "date": "[datetime]"
        },
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
            "date": "[datetime]"
        },
        {
            "id": 1,
            "roleId": 1,
            "status": 1,
            "name": "Administrator",
            "email": "admin@localhost.com",
            "date": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/user?search=Dev', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 4,
            "roleId": 2,
            "status": 1,
            "name": "Developer",
            "email": "developer@localhost.com",
            "points": 10,
            "date": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/user?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 4,
            "roleId": 2,
            "status": 1,
            "name": "Developer",
            "email": "developer@localhost.com",
            "points": 10,
            "date": "[datetime]"
        },
        {
            "id": 3,
            "roleId": 3,
            "status": 2,
            "name": "Disabled",
            "email": "disabled@localhost.com",
            "date": "[datetime]"
        },
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
            "date": "[datetime]"
        },
        {
            "id": 1,
            "roleId": 1,
            "status": 1,
            "name": "Administrator",
            "email": "admin@localhost.com",
            "date": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $metadata = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/user', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'roleId'   => 1,
            'planId'   => 1,
            'status'   => 0,
            'name'     => 'test',
            'email'    => 'test@localhost.com',
            'password' => 'fooo123!',
            'metadata' => $metadata,
        ]));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "success": true,
    "message": "User successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'plan_id', 'status', 'name', 'email', 'password', 'metadata')
            ->from('fusio_user')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(6, $row['id']);
        $this->assertEquals(1, $row['plan_id']);
        $this->assertEquals(0, $row['status']);
        $this->assertEquals('test', $row['name']);
        $this->assertEquals('test@localhost.com', $row['email']);
        $this->assertTrue(password_verify('fooo123!', $row['password']));
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);

        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'user_id', 'scope_id')
            ->from('fusio_user_scope')
            ->where('user_id = :user_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $routes = $this->connection->fetchAllAssociative($sql, ['user_id' => 6]);

        $this->assertEquals(40, count($routes));
    }

    public function testPostNameExists()
    {
        $response = $this->sendRequest('/backend/user', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'roleId'   => 1,
            'status'   => 0,
            'name'     => 'Consumer',
            'email'    => 'test@localhost.com',
            'password' => 'fooo123!',
            'scopes'   => ['foo', 'bar'],
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('User name already exists', $data->message);
    }

    public function testPostEmailExists()
    {
        $response = $this->sendRequest('/backend/user', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'roleId'   => 1,
            'status'   => 0,
            'name'     => 'test',
            'email'    => 'consumer@localhost.com',
            'password' => 'fooo123!',
            'scopes'   => ['foo', 'bar'],
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('User email already exists', $data->message);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/user', 'PUT', array(
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
        $response = $this->sendRequest('/backend/user', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

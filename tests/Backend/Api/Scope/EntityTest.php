<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\Api\Scope;

use Fusio\Impl\Table;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Fixture;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends DbTestCase
{
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = Fixture::getReference('fusio_scope', 'bar')->resolve($this->connection);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/scope/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 55,
    "name": "bar",
    "description": "Bar access",
    "operations": [
        {
            "id": 232,
            "scopeId": 55,
            "operationId": 238,
            "allow": true
        },
        {
            "id": 231,
            "scopeId": 55,
            "operationId": 237,
            "allow": true
        },
        {
            "id": 230,
            "scopeId": 55,
            "operationId": 236,
            "allow": true
        },
        {
            "id": 229,
            "scopeId": 55,
            "operationId": 235,
            "allow": true
        },
        {
            "id": 228,
            "scopeId": 55,
            "operationId": 234,
            "allow": true
        },
        {
            "id": 227,
            "scopeId": 55,
            "operationId": 233,
            "allow": true
        },
        {
            "id": 226,
            "scopeId": 55,
            "operationId": 232,
            "allow": true
        },
        {
            "id": 225,
            "scopeId": 55,
            "operationId": 231,
            "allow": true
        },
        {
            "id": 224,
            "scopeId": 55,
            "operationId": 230,
            "allow": true
        },
        {
            "id": 223,
            "scopeId": 55,
            "operationId": 229,
            "allow": true
        },
        {
            "id": 221,
            "scopeId": 55,
            "operationId": 228,
            "allow": true
        },
        {
            "id": 219,
            "scopeId": 55,
            "operationId": 227,
            "allow": true
        },
        {
            "id": 218,
            "scopeId": 55,
            "operationId": 226,
            "allow": true
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetByName()
    {
        $response = $this->sendRequest('/backend/scope/~bar', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 55,
    "name": "bar",
    "description": "Bar access",
    "operations": [
        {
            "id": 232,
            "scopeId": 55,
            "operationId": 238,
            "allow": true
        },
        {
            "id": 231,
            "scopeId": 55,
            "operationId": 237,
            "allow": true
        },
        {
            "id": 230,
            "scopeId": 55,
            "operationId": 236,
            "allow": true
        },
        {
            "id": 229,
            "scopeId": 55,
            "operationId": 235,
            "allow": true
        },
        {
            "id": 228,
            "scopeId": 55,
            "operationId": 234,
            "allow": true
        },
        {
            "id": 227,
            "scopeId": 55,
            "operationId": 233,
            "allow": true
        },
        {
            "id": 226,
            "scopeId": 55,
            "operationId": 232,
            "allow": true
        },
        {
            "id": 225,
            "scopeId": 55,
            "operationId": 231,
            "allow": true
        },
        {
            "id": 224,
            "scopeId": 55,
            "operationId": 230,
            "allow": true
        },
        {
            "id": 223,
            "scopeId": 55,
            "operationId": 229,
            "allow": true
        },
        {
            "id": 221,
            "scopeId": 55,
            "operationId": 228,
            "allow": true
        },
        {
            "id": 219,
            "scopeId": 55,
            "operationId": 227,
            "allow": true
        },
        {
            "id": 218,
            "scopeId": 55,
            "operationId": 226,
            "allow": true
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        $response = $this->sendRequest('/backend/scope/100', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Could not find scope', $data->message);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/scope/' . $this->id, 'POST', array(
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

        $response = $this->sendRequest('/backend/scope/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'     => 'Test',
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Scope successfully updated",
    "id": "55"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'name', 'metadata')
            ->from('fusio_scope')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals('Test', $row['name']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);
    }

    public function testDelete()
    {
        // delete all scope references to successful delete an scope
        $this->connection->executeStatement('DELETE FROM fusio_app_scope WHERE scope_id = :scope_id', ['scope_id' => $this->id]);
        $this->connection->executeStatement('DELETE FROM fusio_user_scope WHERE scope_id = :scope_id', ['scope_id' => $this->id]);
        $this->connection->executeStatement('DELETE FROM fusio_plan_scope WHERE scope_id = :scope_id', ['scope_id' => $this->id]);

        $response = $this->sendRequest('/backend/scope/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Scope successfully deleted",
    "id": "55"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_scope')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(Table\Scope::STATUS_DELETED, $row['status']);
    }

    public function testDeleteAppScopeAssigned()
    {
        $this->connection->executeStatement('DELETE FROM fusio_user_scope WHERE scope_id = :scope_id', ['scope_id' => $this->id]);

        $response = $this->sendRequest('/backend/scope/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(409, $response->getStatusCode(), $body);
        $this->assertStringStartsWith('Scope is assigned to an app', $data->message);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('fusio_scope')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => $this->id]);

        $this->assertNotEmpty($row);
    }

    public function testDeleteUserScopeAssigned()
    {
        $this->connection->executeStatement('DELETE FROM fusio_app_scope WHERE scope_id = :scope_id', ['scope_id' => $this->id]);

        $response = $this->sendRequest('/backend/scope/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(409, $response->getStatusCode(), $body);
        $this->assertStringStartsWith('Scope is assigned to an user', $data->message);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id')
            ->from('fusio_scope')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => $this->id]);

        $this->assertNotEmpty($row);
    }
}

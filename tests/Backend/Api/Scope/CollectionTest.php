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

use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Fixture;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/backend/scope', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 56,
            "name": "plan_scope",
            "description": "Plan scope access"
        },
        {
            "id": 55,
            "name": "bar",
            "description": "Bar access"
        },
        {
            "id": 54,
            "name": "foo",
            "description": "Foo access",
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 5,
            "name": "default",
            "description": ""
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/scope?search=fo', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 54,
            "name": "foo",
            "description": "Foo access",
            "metadata": {
                "foo": "bar"
            }
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/scope?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 56,
            "name": "plan_scope",
            "description": "Plan scope access"
        },
        {
            "id": 55,
            "name": "bar",
            "description": "Bar access"
        },
        {
            "id": 54,
            "name": "foo",
            "description": "Foo access",
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 5,
            "name": "default",
            "description": ""
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

        $response = $this->sendRequest('/backend/scope', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'        => 'test',
            'description' => 'Test description',
            'operations'  => [[
                'operationId' => Fixture::getReference('fusio_operation', 'test.listFoo')->resolve($this->connection),
                'allow' => true,
            ], [
                'operationId' => Fixture::getReference('fusio_operation', 'inspect.get')->resolve($this->connection),
                'allow' => true,
            ]],
            'metadata'    => $metadata,
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertInstanceOf(\stdClass::class, $data, $body);
        $this->assertTrue($data->success, $body);
        $this->assertEquals('Scope successfully created', $data->success, $body);
        $this->assertNotEmpty($data->id, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'name', 'description', 'metadata')
            ->from('fusio_scope')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => $data->id]);

        $this->assertEquals($data->id, $row['id']);
        $this->assertEquals('test', $row['name']);
        $this->assertEquals('Test description', $row['description']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);

        $sql = $this->connection->createQueryBuilder()
            ->select('scope_id', 'operation_id', 'allow')
            ->from('fusio_scope_operation')
            ->where('scope_id = :scope_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $scopeId = $row['id'];
        $operations = $this->connection->fetchAllAssociative($sql, ['scope_id' => $scopeId]);

        $this->assertEquals([[
            'scope_id' => $scopeId,
            'operation_id' => 237,
            'allow' => 1,
        ], [
            'scope_id' => $scopeId,
            'operation_id' => 235,
            'allow' => 1,
        ]], $operations);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/scope', 'PUT', array(
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
        $response = $this->sendRequest('/backend/scope', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

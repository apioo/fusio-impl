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

namespace Fusio\Impl\Tests\Backend\Api\Operation;

use Fusio\Impl\Table\Operation as TableRoutes;
use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Fixture;
use PSX\Api\OperationInterface;

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

        $this->id = Fixture::getReference('fusio_operation', 'test.listFoo')->resolve($this->connection);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/operation/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 256,
    "status": 1,
    "name": "test.listFoo",
    "scopes": [
        "bar"
    ],
    "active": true,
    "public": true,
    "stability": 1,
    "description": "",
    "httpMethod": "GET",
    "httpPath": "\/foo",
    "httpCode": 200,
    "parameters": {
        "startIndex": {
            "type": "integer"
        },
        "count": {
            "type": "integer"
        }
    },
    "outgoing": "schema:\/\/Collection-Schema",
    "throws": {},
    "action": "action:\/\/Sql-Select-All",
    "costs": 0
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetByName()
    {
        $response = $this->sendRequest('/backend/operation/~test.listFoo', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 256,
    "status": 1,
    "name": "test.listFoo",
    "scopes": [
        "bar"
    ],
    "active": true,
    "public": true,
    "stability": 1,
    "description": "",
    "httpMethod": "GET",
    "httpPath": "\/foo",
    "httpCode": 200,
    "parameters": {
        "startIndex": {
            "type": "integer"
        },
        "count": {
            "type": "integer"
        }
    },
    "outgoing": "schema:\/\/Collection-Schema",
    "throws": {},
    "action": "action:\/\/Sql-Select-All",
    "costs": 0
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        $response = $this->sendRequest('/backend/operation/1000', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Could not find operation', $data->message);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/operation/' . $this->id, 'POST', array(
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

        $response = $this->sendRequest('/backend/operation/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_STABLE,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo',
            'httpCode'   => 201,
            'name'       => 'test.baz',
            'parameters' => [
                'foo' => [
                    'type' => 'string'
                ]
            ],
            'incoming'   => 'Passthru',
            'outgoing'   => 'Passthru',
            'throws'     => [
                500 => 'Passthru',
            ],
            'cost'       => 10,
            'action'     => 'Sql-Select-All',
            'scopes'     => ['foo', 'baz'],
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Operation successfully updated",
    "id": "256"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertOperation($this->connection, OperationInterface::STABILITY_STABLE, 'test.baz', 'GET', '/foo', 201, ['foo', 'baz'], $metadata);
    }

    /**
     * If we are sending a put against a stable operation we are only able to change the stability all other properties
     * should not change
     */
    public function testPutStable()
    {
        $response = $this->sendRequest('/backend/operation/~test.createFoo', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_DEPRECATED,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo/baz',
            'httpCode'   => 200,
            'name'       => 'test.bazzz',
            'parameters' => [
                'foo' => [
                    'type' => 'string'
                ]
            ],
            'incoming'   => 'Passthru',
            'outgoing'   => 'Passthru',
            'throws'     => [
                500 => 'Passthru',
            ],
            'cost'       => 10,
            'action'     => 'Sql-Select-All',
            'scopes'     => ['foo', 'baz'],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Operation successfully updated",
    "id": "257"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertOperation($this->connection, OperationInterface::STABILITY_DEPRECATED, 'test.createFoo', 'POST', '/foo', 201, ['bar']);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/operation/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Operation successfully deleted",
    "id": "256"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_operation')
            ->where('id = ' . $this->id)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals($this->id, $row['id']);
        $this->assertEquals(TableRoutes::STATUS_DELETED, $row['status']);
    }
}

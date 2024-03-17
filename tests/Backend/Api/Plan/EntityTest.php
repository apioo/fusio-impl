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

namespace Fusio\Impl\Tests\Backend\Api\Plan;

use Fusio\Impl\Table;
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
        $response = $this->sendRequest('/backend/plan/1', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 1,
    "status": 1,
    "name": "Plan A",
    "description": "",
    "price": 39.99,
    "points": 500,
    "period": 1,
    "externalId": "price_1L3dOA2Tb35ankTn36cCgliu",
    "scopes": [
        "foo",
        "bar",
        "plan_scope"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetByName()
    {
        $response = $this->sendRequest('/backend/plan/~Plan A', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 1,
    "status": 1,
    "name": "Plan A",
    "description": "",
    "price": 39.99,
    "points": 500,
    "period": 1,
    "externalId": "price_1L3dOA2Tb35ankTn36cCgliu",
    "scopes": [
        "foo",
        "bar",
        "plan_scope"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        $response = $this->sendRequest('/backend/plan/10', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Could not find plan', $data->message);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/plan/1', 'POST', array(
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

        $response = $this->sendRequest('/backend/plan/1', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'        => 'New-Test',
            'description' => 'Test new description',
            'price'       => 49.99,
            'points'      => 4000,
            'externalId'  => 'foobar',
            'scopes'      => ['bar'],
            'metadata'    => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Plan successfully updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'name', 'description', 'price', 'points', 'period_type', 'external_id', 'metadata')
            ->from('fusio_plan')
            ->where('id = 1')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(1, $row['id']);
        $this->assertEquals('New-Test', $row['name']);
        $this->assertEquals('Test new description', $row['description']);
        $this->assertEquals(4999, $row['price']);
        $this->assertEquals(4000, $row['points']);
        $this->assertEquals(1, $row['period_type']);
        $this->assertEquals('foobar', $row['external_id']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);

        // check scopes
        $sql = $this->connection->createQueryBuilder()
            ->select('plan_id', 'scope_id')
            ->from('fusio_plan_scope')
            ->where('plan_id = :plan_id')
            ->getSQL();

        $result = $this->connection->fetchAllAssociative($sql, ['plan_id' => 1]);

        $this->assertEquals(1, count($result));
        $this->assertEquals(49, $result[0]['scope_id']);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/plan/1', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Plan successfully deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_plan')
            ->where('id = 1')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(1, $row['id']);
        $this->assertEquals(Table\Plan::STATUS_DELETED, $row['status']);
    }
}

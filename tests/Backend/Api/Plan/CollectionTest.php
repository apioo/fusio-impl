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

use Fusio\Engine\Model\ProductInterface;
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
        $response = $this->sendRequest('/backend/plan', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 1,
            "status": 1,
            "name": "Plan A",
            "description": "",
            "price": 39.99,
            "points": 500,
            "period": 1,
            "externalId": "price_1L3dOA2Tb35ankTn36cCgliu",
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 2,
            "status": 1,
            "name": "Plan B",
            "description": "",
            "price": 49.99,
            "points": 1000
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

        $response = $this->sendRequest('/backend/plan', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'        => 'Plan D',
            'description' => 'Test description',
            'price'       => 59.99,
            'points'      => 1000,
            'period'      => ProductInterface::INTERVAL_SUBSCRIPTION,
            'externalId'  => 'price_1L3dOA2Tb35ankTn36cCgliu',
            'scopes'      => ['foo'],
            'metadata'    => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Plan successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status', 'name', 'description', 'price', 'points', 'period_type', 'external_id', 'metadata')
            ->from('fusio_plan')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(3, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('Plan D', $row['name']);
        $this->assertEquals('Test description', $row['description']);
        $this->assertEquals(5999, $row['price']);
        $this->assertEquals(1000, $row['points']);
        $this->assertEquals(ProductInterface::INTERVAL_SUBSCRIPTION, $row['period_type']);
        $this->assertEquals('price_1L3dOA2Tb35ankTn36cCgliu', $row['external_id']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);

        // check scopes
        $sql = $this->connection->createQueryBuilder()
            ->select('plan_id', 'scope_id')
            ->from('fusio_plan_scope')
            ->where('plan_id = :plan_id')
            ->getSQL();

        $result = $this->connection->fetchAllAssociative($sql, ['plan_id' => $row['id']]);

        $this->assertEquals(1, count($result));
        $this->assertEquals(48, $result[0]['scope_id']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/plan', 'PUT', array(
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
        $response = $this->sendRequest('/backend/plan', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

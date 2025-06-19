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

namespace Fusio\Impl\Tests\Backend\Api\Rate;

use Fusio\Impl\Tests\DbTestCase;

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
        $response = $this->sendRequest('/backend/rate', 'GET', array(
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
            "id": 4,
            "status": 1,
            "priority": 10,
            "name": "gold",
            "rateLimit": 16,
            "timespan": "P1M"
        },
        {
            "id": 3,
            "status": 1,
            "priority": 5,
            "name": "silver",
            "rateLimit": 8,
            "timespan": "P1M",
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 2,
            "status": 1,
            "priority": 4,
            "name": "Default-Anonymous",
            "rateLimit": 900,
            "timespan": "PT1H"
        },
        {
            "id": 1,
            "status": 1,
            "priority": 0,
            "name": "Default",
            "rateLimit": 3600,
            "timespan": "PT1H"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/rate?search=gol', 'GET', array(
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
            "id": 4,
            "status": 1,
            "priority": 10,
            "name": "gold",
            "rateLimit": 16,
            "timespan": "P1M"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/rate?count=80', 'GET', array(
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
            "id": 4,
            "status": 1,
            "priority": 10,
            "name": "gold",
            "rateLimit": 16,
            "timespan": "P1M"
        },
        {
            "id": 3,
            "status": 1,
            "priority": 5,
            "name": "silver",
            "rateLimit": 8,
            "timespan": "P1M",
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 2,
            "status": 1,
            "priority": 4,
            "name": "Default-Anonymous",
            "rateLimit": 900,
            "timespan": "PT1H"
        },
        {
            "id": 1,
            "status": 1,
            "priority": 0,
            "name": "Default",
            "rateLimit": 3600,
            "timespan": "PT1H"
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

        $response = $this->sendRequest('/backend/rate', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'priority'  => 2,
            'name'      => 'Premium',
            'rateLimit' => 20,
            'timespan'  => 'PT2H',
            'allocation' => [[
                'operationId' => 1,
                'authenticated' => true,
            ]],
            'metadata'  => $metadata,
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertInstanceOf(\stdClass::class, $data, $body);
        $this->assertTrue($data->success, $body);
        $this->assertEquals('Rate successfully created', $data->success, $body);
        $this->assertNotEmpty($data->id, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status', 'priority', 'name', 'rate_limit', 'timespan', 'metadata')
            ->from('fusio_rate')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => $data->id]);

        $this->assertEquals($data->id, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals(2, $row['priority']);
        $this->assertEquals('Premium', $row['name']);
        $this->assertEquals(20, $row['rate_limit']);
        $this->assertEquals('PT2H', $row['timespan']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);

        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'rate_id', 'operation_id', 'user_id', 'plan_id', 'app_id', 'authenticated')
            ->from('fusio_rate_allocation')
            ->where('rate_id = :rate_id')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $result = $this->connection->fetchAllAssociative($sql, ['rate_id' => $row['id']]);

        $this->assertNotEmpty($result);
        $this->assertEquals(5, $result[0]['id']);
        $this->assertEquals(5, $result[0]['rate_id']);
        $this->assertEquals(1, $result[0]['operation_id']);
        $this->assertEquals(null, $result[0]['user_id']);
        $this->assertEquals(null, $result[0]['plan_id']);
        $this->assertEquals(null, $result[0]['app_id']);
        $this->assertEquals(1, $result[0]['authenticated']);
    }

    public function testPostSpecific()
    {
        $response = $this->sendRequest('/backend/rate', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'priority'  => 2,
            'name'      => 'Premium',
            'rateLimit' => 20,
            'timespan'  => 'PT2H',
            'allocation'  => [[
                'operationId' => 1,
                'userId' => 1,
                'planId' => 1,
                'appId' => 1,
                'authenticated' => true,
            ]],
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertInstanceOf(\stdClass::class, $data, $body);
        $this->assertTrue($data->success, $body);
        $this->assertEquals('Rate successfully created', $data->success, $body);
        $this->assertNotEmpty($data->id, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status', 'priority', 'name', 'rate_limit', 'timespan')
            ->from('fusio_rate')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => $data->id]);

        $this->assertEquals($data->id, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals(2, $row['priority']);
        $this->assertEquals('Premium', $row['name']);
        $this->assertEquals(20, $row['rate_limit']);
        $this->assertEquals('PT2H', $row['timespan']);

        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'rate_id', 'operation_id', 'user_id', 'plan_id', 'app_id', 'authenticated')
            ->from('fusio_rate_allocation')
            ->where('rate_id = :rate_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $result = $this->connection->fetchAllAssociative($sql, ['rate_id' => $row['id']]);

        $this->assertNotEmpty($result);
        $this->assertCount(1, $result);
        $this->assertEquals($data->id, $result[0]['rate_id']);
        $this->assertEquals(1, $result[0]['operation_id']);
        $this->assertEquals(1, $result[0]['user_id']);
        $this->assertEquals(1, $result[0]['plan_id']);
        $this->assertEquals(1, $result[0]['app_id']);
        $this->assertEquals(1, $result[0]['authenticated']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/rate', 'PUT', array(
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
        $response = $this->sendRequest('/backend/rate', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

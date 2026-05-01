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

namespace Fusio\Impl\Tests\Backend\Api\Cronjob;

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
    public function testGet(): void
    {
        $response = $this->sendRequest('/backend/cronjob', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 3,
            "status": 1,
            "name": "Second-Cron",
            "cron": "* * * * *",
            "action": "Sql-Select-All",
            "executeDate": "2015-02-27T19:59:15Z",
            "exitCode": 0,
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 2,
            "status": 1,
            "name": "Test-Cron",
            "cron": "* * * * *",
            "action": "Sql-Select-All",
            "executeDate": "2015-02-27T19:59:15Z",
            "exitCode": 0,
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

    public function testGetSearch(): void
    {
        $response = $this->sendRequest('/backend/cronjob?search=Test', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 2,
            "status": 1,
            "name": "Test-Cron",
            "cron": "* * * * *",
            "action": "Sql-Select-All",
            "executeDate": "2015-02-27T19:59:15Z",
            "exitCode": 0,
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

    public function testGetTaxonomy(): void
    {
        $response = $this->sendRequest('/backend/cronjob?search=taxonomy_id:1', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 3,
            "status": 1,
            "name": "Second-Cron",
            "cron": "* * * * *",
            "action": "Sql-Select-All",
            "executeDate": "2015-02-27T19:59:15Z",
            "exitCode": 0,
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

    public function testGetCount(): void
    {
        $response = $this->sendRequest('/backend/cronjob?count=80', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 3,
            "status": 1,
            "name": "Second-Cron",
            "cron": "* * * * *",
            "action": "Sql-Select-All",
            "executeDate": "2015-02-27T19:59:15Z",
            "exitCode": 0,
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 2,
            "status": 1,
            "name": "Test-Cron",
            "cron": "* * * * *",
            "action": "Sql-Select-All",
            "executeDate": "2015-02-27T19:59:15Z",
            "exitCode": 0,
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

    public function testPost(): void
    {
        $metadata = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/cronjob', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'name'     => 'New-Cron',
            'cron'     => '5 * * * *',
            'action'   => 'action://Sql-Select-All',
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Cronjob successfully created",
    "id": "4"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'name', 'cron', 'action', 'metadata')
            ->from('fusio_cronjob')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(4, $row['id']);
        $this->assertEquals('New-Cron', $row['name']);
        $this->assertEquals('5 * * * *', $row['cron']);
        $this->assertEquals('action://Sql-Select-All', $row['action']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);
    }

    public function testPut(): void
    {
        $response = $this->sendRequest('/backend/cronjob', 'PUT', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete(): void
    {
        $response = $this->sendRequest('/backend/cronjob', 'DELETE', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

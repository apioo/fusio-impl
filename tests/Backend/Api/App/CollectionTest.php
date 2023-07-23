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

namespace Fusio\Impl\Tests\Backend\Api\App;

use Fusio\Impl\Table;
use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Sql\TableManagerInterface;

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
        $response = $this->sendRequest('/backend/app', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<JSON
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 4,
            "userId": 2,
            "status": 2,
            "name": "Pending",
            "appKey": "[uuid]",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "userId": 2,
            "status": 1,
            "name": "Foo-App",
            "appKey": "[uuid]",
            "metadata": {
                "foo": "bar"
            },
            "date": "[datetime]"
        },
        {
            "id": 2,
            "userId": 1,
            "status": 1,
            "name": "Developer",
            "appKey": "[uuid]",
            "date": "[datetime]"
        },
        {
            "id": 1,
            "userId": 1,
            "status": 1,
            "name": "Backend",
            "appKey": "[uuid]",
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
        $response = $this->sendRequest('/backend/app?search=Foo', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<JSON
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 3,
            "userId": 2,
            "status": 1,
            "name": "Foo-App",
            "appKey": "[uuid]",
            "metadata": {
                "foo": "bar"
            },
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
        $response = $this->sendRequest('/backend/app?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<JSON
{
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 4,
            "userId": 2,
            "status": 2,
            "name": "Pending",
            "appKey": "[uuid]",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "userId": 2,
            "status": 1,
            "name": "Foo-App",
            "appKey": "[uuid]",
            "metadata": {
                "foo": "bar"
            },
            "date": "[datetime]"
        },
        {
            "id": 2,
            "userId": 1,
            "status": 1,
            "name": "Developer",
            "appKey": "[uuid]",
            "date": "[datetime]"
        },
        {
            "id": 1,
            "userId": 1,
            "status": 1,
            "name": "Backend",
            "appKey": "[uuid]",
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
        $metadata = ['foo' => 'bar'];

        $response = $this->sendRequest('/backend/app', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status'   => 0,
            'userId'   => 1,
            'name'     => 'Foo',
            'url'      => 'http://google.com',
            'scopes'   => ['foo', 'bar'],
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "App successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status', 'user_id', 'name', 'url', 'parameters', 'metadata')
            ->from('fusio_app')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(6, $row['id']);
        $this->assertEquals(0, $row['status']);
        $this->assertEquals(1, $row['user_id']);
        $this->assertEquals('Foo', $row['name']);
        $this->assertEquals('http://google.com', $row['url']);
        $this->assertEquals('', $row['parameters']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);

        $scopes = Environment::getService(TableManagerInterface::class)->getTable(Table\App\Scope::class)->getAvailableScopes(6);
        $scopes = Table\Scope::getNames($scopes);

        $this->assertEquals(['foo', 'bar'], $scopes);
    }

    public function testPostWithParameters()
    {
        $response = $this->sendRequest('/backend/app', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status'     => 0,
            'userId'     => 1,
            'name'       => 'Foo',
            'url'        => 'http://google.com',
            'parameters' => 'foo=bar&bar=1',
            'scopes'     => ['foo', 'bar']
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "App successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status', 'user_id', 'name', 'url', 'parameters')
            ->from('fusio_app')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(6, $row['id']);
        $this->assertEquals(0, $row['status']);
        $this->assertEquals(1, $row['user_id']);
        $this->assertEquals('Foo', $row['name']);
        $this->assertEquals('http://google.com', $row['url']);
        $this->assertEquals('foo=bar&bar=1', $row['parameters']);

        $scopes = Environment::getService(TableManagerInterface::class)->getTable(Table\App\Scope::class)->getAvailableScopes(6);
        $scopes = Table\Scope::getNames($scopes);

        $this->assertEquals(['foo', 'bar'], $scopes);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/app', 'PUT', array(
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
        $response = $this->sendRequest('/backend/app', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

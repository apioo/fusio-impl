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

namespace Fusio\Impl\Tests\Backend\Api\Connection;

use Fusio\Adapter\Sql\Connection\SqlAdvanced;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;

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
        $response = $this->sendRequest('/backend/connection', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 7,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 7,
            "status": 1,
            "name": "StarwarsSDK",
            "class": "Fusio.Adapter.SdkFabric.Connection.Starwars"
        },
        {
            "id": 6,
            "status": 1,
            "name": "FusioHttpClient",
            "class": "Fusio.Adapter.Http.Connection.Http"
        },
        {
            "id": 5,
            "status": 1,
            "name": "LocalFilesystem",
            "class": "Fusio.Adapter.File.Connection.Filesystem"
        },
        {
            "id": 4,
            "status": 1,
            "name": "Worker",
            "class": "Fusio.Adapter.Worker.Connection.Worker"
        },
        {
            "id": 3,
            "status": 1,
            "name": "Paypal",
            "class": "Fusio.Impl.Tests.Adapter.Test.PaypalConnection"
        },
        {
            "id": 2,
            "status": 1,
            "name": "Test",
            "class": "Fusio.Impl.Connection.Native",
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 1,
            "status": 1,
            "name": "System",
            "class": "Fusio.Impl.Connection.System"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/connection?search=yst', 'GET', array(
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
            "id": 5,
            "status": 1,
            "name": "LocalFilesystem",
            "class": "Fusio.Adapter.File.Connection.Filesystem"
        },
        {
            "id": 1,
            "status": 1,
            "name": "System",
            "class": "Fusio.Impl.Connection.System"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetClass()
    {
        $response = $this->sendRequest('/backend/connection?class=Fusio.Impl.Tests.Adapter.Test.PaypalConnection', 'GET', array(
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
            "id": 3,
            "status": 1,
            "name": "Paypal",
            "class": "Fusio.Impl.Tests.Adapter.Test.PaypalConnection"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetClasses()
    {
        $response = $this->sendRequest('/backend/connection?class=Fusio.Impl.Tests.Adapter.Test.PaypalConnection,Fusio.Impl.Connection.System', 'GET', array(
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
            "id": 3,
            "status": 1,
            "name": "Paypal",
            "class": "Fusio.Impl.Tests.Adapter.Test.PaypalConnection"
        },
        {
            "id": 1,
            "status": 1,
            "name": "System",
            "class": "Fusio.Impl.Connection.System"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/connection?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 7,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 7,
            "status": 1,
            "name": "StarwarsSDK",
            "class": "Fusio.Adapter.SdkFabric.Connection.Starwars"
        },
        {
            "id": 6,
            "status": 1,
            "name": "FusioHttpClient",
            "class": "Fusio.Adapter.Http.Connection.Http"
        },
        {
            "id": 5,
            "status": 1,
            "name": "LocalFilesystem",
            "class": "Fusio.Adapter.File.Connection.Filesystem"
        },
        {
            "id": 4,
            "status": 1,
            "name": "Worker",
            "class": "Fusio.Adapter.Worker.Connection.Worker"
        },
        {
            "id": 3,
            "status": 1,
            "name": "Paypal",
            "class": "Fusio.Impl.Tests.Adapter.Test.PaypalConnection"
        },
        {
            "id": 2,
            "status": 1,
            "name": "Test",
            "class": "Fusio.Impl.Connection.Native",
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 1,
            "status": 1,
            "name": "System",
            "class": "Fusio.Impl.Connection.System"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $config = [
            'url' => Environment::getConfig('psx_connection'),
        ];

        $metadata = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/connection', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'     => 'Foo',
            'class'    => SqlAdvanced::class,
            'config'   => $config,
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Connection successfully created",
    "id": "8"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'name', 'class', 'config', 'metadata')
            ->from('fusio_connection')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(8, $row['id']);
        $this->assertEquals('Foo', $row['name']);
        $this->assertEquals(ClassName::serialize(SqlAdvanced::class), $row['class']);
        $this->assertNotEmpty($row['config']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/connection', 'PUT', array(
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
        $response = $this->sendRequest('/backend/connection', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

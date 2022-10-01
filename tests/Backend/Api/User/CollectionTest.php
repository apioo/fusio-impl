<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Tests\Backend\Api\User;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/user', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/collection.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
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
            "provider": 1,
            "status": 1,
            "name": "Developer",
            "email": "developer@localhost.com",
            "points": 10,
            "date": "[datetime]"
        },
        {
            "id": 3,
            "roleId": 3,
            "provider": 1,
            "status": 2,
            "name": "Disabled",
            "email": "disabled@localhost.com",
            "date": "[datetime]"
        },
        {
            "id": 2,
            "roleId": 3,
            "planId": 1,
            "provider": 1,
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
            "provider": 1,
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
            "provider": 1,
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
            "provider": 1,
            "status": 1,
            "name": "Developer",
            "email": "developer@localhost.com",
            "points": 10,
            "date": "[datetime]"
        },
        {
            "id": 3,
            "roleId": 3,
            "provider": 1,
            "status": 2,
            "name": "Disabled",
            "email": "disabled@localhost.com",
            "date": "[datetime]"
        },
        {
            "id": 2,
            "roleId": 3,
            "planId": 1,
            "provider": 1,
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
            "provider": 1,
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
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'plan_id', 'status', 'name', 'email', 'password', 'metadata')
            ->from('fusio_user')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(6, $row['id']);
        $this->assertEquals(1, $row['plan_id']);
        $this->assertEquals(0, $row['status']);
        $this->assertEquals('test', $row['name']);
        $this->assertEquals('test@localhost.com', $row['email']);
        $this->assertTrue(password_verify('fooo123!', $row['password']));
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'user_id', 'scope_id')
            ->from('fusio_user_scope')
            ->where('user_id = :user_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $routes = Environment::getService('connection')->fetchAll($sql, ['user_id' => 6]);

        $this->assertEquals(40, count($routes));
    }

    public function testPostNameExists()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

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

        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "User name already exists"
}
JSON;

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPostEmailExists()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

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

        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "User email already exists"
}
JSON;

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
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

        $this->assertEquals(405, $response->getStatusCode(), $body);
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

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

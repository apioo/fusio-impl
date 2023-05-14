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

namespace Fusio\Impl\Tests\Backend\Api\Role;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
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
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/role', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 3,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 1,
            "categoryId": 1,
            "status": 1,
            "name": "Administrator"
        },
        {
            "id": 2,
            "categoryId": 1,
            "status": 1,
            "name": "Backend"
        },
        {
            "id": 3,
            "categoryId": 1,
            "status": 1,
            "name": "Consumer"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/backend/role?search=ack', 'GET', array(
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
            "id": 2,
            "categoryId": 1,
            "status": 1,
            "name": "Backend"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/role?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 3,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 1,
            "categoryId": 1,
            "status": 1,
            "name": "Administrator"
        },
        {
            "id": 2,
            "categoryId": 1,
            "status": 1,
            "name": "Backend"
        },
        {
            "id": 3,
            "categoryId": 1,
            "status": 1,
            "name": "Consumer"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/role', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'categoryId' => 1,
            'name'       => 'Developer',
            'scopes'     => ['backend.action', 'backend.schema'],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Role successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'category_id', 'status', 'name')
            ->from('fusio_role')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(1, $row['category_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('Developer', $row['name']);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('scope_id')
            ->from('fusio_role_scope')
            ->where('role_id = :role_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $result = Environment::getService('connection')->fetchAll($sql, ['role_id' => $row['id']]);

        $this->assertEquals(2, count($result));
        $this->assertEquals(6, $result[0]['scope_id']);
        $this->assertEquals(23, $result[1]['scope_id']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/role', 'PUT', array(
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
        $response = $this->sendRequest('/backend/role', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

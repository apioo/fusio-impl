<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\Api\Scope;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/backend/scope', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/collection.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/scope', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 34,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 34,
            "name": "bar",
            "description": "Bar access"
        },
        {
            "id": 33,
            "name": "foo",
            "description": "Foo access"
        },
        {
            "id": 32,
            "name": "consumer.user",
            "description": ""
        },
        {
            "id": 31,
            "name": "consumer.transaction",
            "description": ""
        },
        {
            "id": 30,
            "name": "consumer.subscription",
            "description": ""
        },
        {
            "id": 29,
            "name": "consumer.scope",
            "description": ""
        },
        {
            "id": 28,
            "name": "consumer.plan",
            "description": ""
        },
        {
            "id": 27,
            "name": "consumer.grant",
            "description": ""
        },
        {
            "id": 26,
            "name": "consumer.event",
            "description": ""
        },
        {
            "id": 25,
            "name": "consumer.app",
            "description": ""
        },
        {
            "id": 24,
            "name": "backend.user",
            "description": ""
        },
        {
            "id": 23,
            "name": "backend.transaction",
            "description": ""
        },
        {
            "id": 22,
            "name": "backend.statistic",
            "description": ""
        },
        {
            "id": 21,
            "name": "backend.sdk",
            "description": ""
        },
        {
            "id": 20,
            "name": "backend.scope",
            "description": ""
        },
        {
            "id": 19,
            "name": "backend.schema",
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
            "id": 33,
            "name": "foo",
            "description": "Foo access"
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
    "totalResults": 34,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 34,
            "name": "bar",
            "description": "Bar access"
        },
        {
            "id": 33,
            "name": "foo",
            "description": "Foo access"
        },
        {
            "id": 32,
            "name": "consumer.user",
            "description": ""
        },
        {
            "id": 31,
            "name": "consumer.transaction",
            "description": ""
        },
        {
            "id": 30,
            "name": "consumer.subscription",
            "description": ""
        },
        {
            "id": 29,
            "name": "consumer.scope",
            "description": ""
        },
        {
            "id": 28,
            "name": "consumer.plan",
            "description": ""
        },
        {
            "id": 27,
            "name": "consumer.grant",
            "description": ""
        },
        {
            "id": 26,
            "name": "consumer.event",
            "description": ""
        },
        {
            "id": 25,
            "name": "consumer.app",
            "description": ""
        },
        {
            "id": 24,
            "name": "backend.user",
            "description": ""
        },
        {
            "id": 23,
            "name": "backend.transaction",
            "description": ""
        },
        {
            "id": 22,
            "name": "backend.statistic",
            "description": ""
        },
        {
            "id": 21,
            "name": "backend.sdk",
            "description": ""
        },
        {
            "id": 20,
            "name": "backend.scope",
            "description": ""
        },
        {
            "id": 19,
            "name": "backend.schema",
            "description": ""
        },
        {
            "id": 18,
            "name": "backend.routes",
            "description": ""
        },
        {
            "id": 17,
            "name": "backend.rate",
            "description": ""
        },
        {
            "id": 16,
            "name": "backend.plan",
            "description": ""
        },
        {
            "id": 15,
            "name": "backend.marketplace",
            "description": ""
        },
        {
            "id": 14,
            "name": "backend.log",
            "description": ""
        },
        {
            "id": 13,
            "name": "backend.import",
            "description": ""
        },
        {
            "id": 12,
            "name": "backend.event",
            "description": ""
        },
        {
            "id": 11,
            "name": "backend.dashboard",
            "description": ""
        },
        {
            "id": 10,
            "name": "backend.cronjob",
            "description": ""
        },
        {
            "id": 9,
            "name": "backend.connection",
            "description": ""
        },
        {
            "id": 8,
            "name": "backend.config",
            "description": ""
        },
        {
            "id": 7,
            "name": "backend.audit",
            "description": ""
        },
        {
            "id": 6,
            "name": "backend.app",
            "description": ""
        },
        {
            "id": 5,
            "name": "backend.action",
            "description": ""
        },
        {
            "id": 4,
            "name": "backend.account",
            "description": ""
        },
        {
            "id": 3,
            "name": "authorization",
            "description": "Authorization API endpoint"
        },
        {
            "id": 2,
            "name": "consumer",
            "description": "Global access to the consumer API"
        },
        {
            "id": 1,
            "name": "backend",
            "description": "Global access to the backend API"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/scope', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'        => 'test',
            'description' => 'Test description',
            'routes' => [[
                'routeId' => 1,
                'allow'   => true,
                'methods' => 'GET|POST|PUT|PATCH|DELETE',
            ], [
                'routeId' => 2,
                'allow'   => true,
                'methods' => 'GET|POST|PUT|PATCH|DELETE',
            ]]
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Scope successful created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'name', 'description')
            ->from('fusio_scope')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(35, $row['id']);
        $this->assertEquals('test', $row['name']);
        $this->assertEquals('Test description', $row['description']);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'scope_id', 'route_id', 'allow', 'methods')
            ->from('fusio_scope_routes')
            ->where('scope_id = :scope_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $routes = Environment::getService('connection')->fetchAll($sql, ['scope_id' => 35]);

        $this->assertEquals([[
            'id'       => 100,
            'scope_id' => 35,
            'route_id' => 2,
            'allow'    => 1,
            'methods'  => 'GET|POST|PUT|PATCH|DELETE',
        ], [
            'id'       => 99,
            'scope_id' => 35,
            'route_id' => 1,
            'allow'    => 1,
            'methods'  => 'GET|POST|PUT|PATCH|DELETE',
        ]], $routes);
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

        $this->assertEquals(405, $response->getStatusCode(), $body);
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

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

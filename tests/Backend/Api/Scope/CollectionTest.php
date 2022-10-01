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
        $response = $this->sendRequest('/system/doc/*/backend/scope', 'GET', array(
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
    "totalResults": 4,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 44,
            "name": "plan_scope",
            "description": "Plan scope access"
        },
        {
            "id": 43,
            "name": "bar",
            "description": "Bar access"
        },
        {
            "id": 42,
            "name": "foo",
            "description": "Foo access",
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 4,
            "name": "default",
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
            "id": 42,
            "name": "foo",
            "description": "Foo access",
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

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/scope?count=80', 'GET', array(
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
            "id": 44,
            "name": "plan_scope",
            "description": "Plan scope access"
        },
        {
            "id": 43,
            "name": "bar",
            "description": "Bar access"
        },
        {
            "id": 42,
            "name": "foo",
            "description": "Foo access",
            "metadata": {
                "foo": "bar"
            }
        },
        {
            "id": 4,
            "name": "default",
            "description": ""
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

        $response = $this->sendRequest('/backend/scope', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'        => 'test',
            'description' => 'Test description',
            'routes'      => [[
                'routeId' => Fixture::getId('fusio_routes', '/foo'),
                'allow'   => true,
                'methods' => 'GET|POST|PUT|PATCH|DELETE',
            ], [
                'routeId' => Fixture::getId('fusio_routes', '/inspect/:foo'),
                'allow'   => true,
                'methods' => 'GET|POST|PUT|PATCH|DELETE',
            ]],
            'metadata'    => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Scope successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'name', 'description', 'metadata')
            ->from('fusio_scope')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(45, $row['id']);
        $this->assertEquals('test', $row['name']);
        $this->assertEquals('Test description', $row['description']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('scope_id', 'route_id', 'allow', 'methods')
            ->from('fusio_scope_routes')
            ->where('scope_id = :scope_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $scopeId = 43;
        $routes = Environment::getService('connection')->fetchAll($sql, ['scope_id' => $scopeId]);

        $this->assertEquals([[
            'scope_id' => $scopeId,
            'route_id' => 117,
            'allow'    => 1,
            'methods'  => 'GET|POST|PUT|PATCH|DELETE',
        ], [
            'scope_id' => $scopeId,
            'route_id' => 116,
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

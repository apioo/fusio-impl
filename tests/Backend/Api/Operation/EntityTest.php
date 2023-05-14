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

namespace Fusio\Impl\Tests\Backend\Api\Operation;

use Fusio\Impl\Table\Operation as TableRoutes;
use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = Fixture::getId('fusio_operation', 'test.listFoo');
    }

    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/operation/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": {$this->id},
    "status": 1,
    "path": "\/foo",
    "controller": "Fusio\\\\Impl\\\\Controller\\\\SchemaApiController",
    "scopes": [
        "bar"
    ],
    "config": [
        {
            "version": 1,
            "status": 4,
            "methods": {
                "GET": {
                    "active": true,
                    "public": true,
                    "operationId": "listFoo",
                    "responses": {
                        "200": "Collection-Schema"
                    },
                    "action": "Sql-Select-All"
                },
                "POST": {
                    "active": true,
                    "public": false,
                    "operationId": "createFoo",
                    "request": "Entry-Schema",
                    "responses": {
                        "201": "Passthru"
                    },
                    "action": "Sql-Insert",
                    "costs": 1
                }
            }
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetByName()
    {
        $response = $this->sendRequest('/backend/operation/~' . urlencode('test.listFoo'), 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": {$this->id},
    "status": 1,
    "path": "\/foo",
    "controller": "Fusio\\\\Impl\\\\Controller\\\\SchemaApiController",
    "scopes": [
        "bar"
    ],
    "config": [
        {
            "version": 1,
            "status": 4,
            "methods": {
                "GET": {
                    "active": true,
                    "public": true,
                    "operationId": "listFoo",
                    "responses": {
                        "200": "Collection-Schema"
                    },
                    "action": "Sql-Select-All"
                },
                "POST": {
                    "active": true,
                    "public": false,
                    "operationId": "createFoo",
                    "request": "Entry-Schema",
                    "responses": {
                        "201": "Passthru"
                    },
                    "action": "Sql-Insert",
                    "costs": 1
                }
            }
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/backend/operation/1000', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Could not find route"
}
JSON;

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/operation/' . $this->id, 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $metadata = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/operation/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path'     => '/foo',
            'scopes'   => ['foo', 'baz'],
            'config'   => [[
                'version' => 1,
                'status'  => 4,
                'methods' => [
                    'GET' => [
                        'active'     => true,
                        'public'     => true,
                        'parameters' => 'Collection-Schema',
                        'responses'  => [
                            '200'    => 'Passthru'
                        ],
                        'costs'      => 16,
                        'action'     => 'Sql-Table',
                    ],
                    'POST' => [
                        'active'     => true,
                        'public'     => false,
                        'request'    => 'Passthru',
                        'responses'  => [
                            '201'    => 'Passthru'
                        ],
                        'action'     => 'Sql-Table',
                    ],
                ],
            ]],
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Operation successfully updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertRoute('/foo', ['foo', 'baz'], [[
            'method'       => 'GET',
            'version'      => 1,
            'status'       => 4,
            'active'       => true,
            'public'       => true,
            'description'  => '',
            'operation_id' => 'get.foo',
            'parameters'   => 'Collection-Schema',
            'request'      => null,
            'responses'    => [
                '200'      => 'Passthru'
            ],
            'action'       => 'Sql-Table',
            'costs'        => 16,
        ], [
            'method'       => 'POST',
            'version'      => 1,
            'status'       => 4,
            'active'       => true,
            'public'       => false,
            'description'  => '',
            'operation_id' => 'post.foo',
            'parameters'   => null,
            'request'      => 'Passthru',
            'responses'    => [
                '201'      => 'Passthru'
            ],
            'action'       => 'Sql-Table',
            'costs'        => 0,
        ]], $metadata);
    }

    public function testPutDeploy()
    {
        $response = $this->sendRequest('/backend/operation/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path'   => '/foo',
            'config' => [[
                'version' => 1,
                'status'  => Resource::STATUS_ACTIVE,
            ]],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Operation successfully updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertRoute('/foo', ['bar'], [[
            'method'       => 'GET',
            'version'      => 1,
            'status'       => 1,
            'active'       => true,
            'public'       => true,
            'description'  => '',
            'operation_id' => 'listFoo',
            'parameters'   => null,
            'request'      => null,
            'responses'    => [
                '200'      => 'Collection-Schema'
            ],
            'action'       => 'Sql-Select-All',
            'costs'        => 0,
        ], [
            'method'       => 'POST',
            'version'      => 1,
            'status'       => 1,
            'active'       => true,
            'public'       => false,
            'description'  => '',
            'operation_id' => 'createFoo',
            'parameters'   => null,
            'request'      => 'Entry-Schema',
            'responses'    => [
                '201'      => 'Passthru'
            ],
            'action'       => 'Sql-Insert',
            'costs'        => 1,
        ]]);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/operation/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Operation successfully deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_operation')
            ->where('id = ' . $this->id)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals($this->id, $row['id']);
        $this->assertEquals(TableRoutes::STATUS_DELETED, $row['status']);
    }
}

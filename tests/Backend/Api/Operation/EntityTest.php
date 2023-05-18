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
use PSX\Api\OperationInterface;
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
    "id": 173,
    "status": 1,
    "active": 1,
    "public": 0,
    "stability": 1,
    "httpMethod": "GET",
    "httpPath": "\/foo",
    "name": "test.listFoo",
    "parameters": [],
    "outgoing": "Collection-Schema",
    "throws": [],
    "action": "Sql-Select-All",
    "costs": 0
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetByName()
    {
        $response = $this->sendRequest('/backend/operation/~test.listFoo', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 173,
    "status": 1,
    "active": 1,
    "public": 0,
    "stability": 1,
    "httpMethod": "GET",
    "httpPath": "\/foo",
    "name": "test.listFoo",
    "parameters": [],
    "outgoing": "Collection-Schema",
    "throws": [],
    "action": "Sql-Select-All",
    "costs": 0
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        $response = $this->sendRequest('/backend/operation/1000', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Could not find operation', $data->message);
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

        $this->assertEquals(404, $response->getStatusCode(), $body);
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
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_STABLE,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo',
            'httpCode'   => 200,
            'name'       => 'test.baz',
            'parameters' => [
                'foo' => [
                    'type' => 'string'
                ]
            ],
            'incoming'   => 'Passthru',
            'outgoing'   => 'Passthru',
            'throws'     => [
                500 => 'Passthru',
            ],
            'cost'       => 10,
            'action'     => 'Sql-Select-All',
            'scopes'     => ['foo', 'baz'],
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
        Assert::assertOperation($this->connection, OperationInterface::STABILITY_STABLE, 'test.baz', 'GET', '/foo', ['foo', 'baz'], $metadata);
    }

    /**
     * If we are sending a put against a stable operation we are only able to change the stability all other properties
     * should not change
     */
    public function testPutStable()
    {
        $response = $this->sendRequest('/backend/operation/~test.createFoo', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'active'     => true,
            'public'     => true,
            'stability'  => OperationInterface::STABILITY_DEPRECATED,
            'httpMethod' => 'GET',
            'httpPath'   => '/foo',
            'httpCode'   => 200,
            'name'       => 'test.baz',
            'parameters' => [
                'foo' => [
                    'type' => 'string'
                ]
            ],
            'incoming'   => 'Passthru',
            'outgoing'   => 'Passthru',
            'throws'     => [
                500 => 'Passthru',
            ],
            'cost'       => 10,
            'action'     => 'Sql-Select-All',
            'scopes'     => ['foo', 'baz'],
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
        Assert::assertOperation($this->connection, OperationInterface::STABILITY_DEPRECATED, 'test.createFoo', 'POST', '/foo', ['foo', 'baz']);
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
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_operation')
            ->where('id = ' . $this->id)
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals($this->id, $row['id']);
        $this->assertEquals(TableRoutes::STATUS_DELETED, $row['status']);
    }
}

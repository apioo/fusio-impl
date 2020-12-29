<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    private $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = Fixture::getId('fusio_scope', 'bar');
    }

    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/scope/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/entity.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/scope/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 37,
    "name": "bar",
    "description": "Bar access",
    "routes": [
        {
            "id": 101,
            "scopeId": 37,
            "routeId": 113,
            "allow": 1,
            "methods": "GET|POST|PUT|PATCH|DELETE"
        },
        {
            "id": 99,
            "scopeId": 37,
            "routeId": 112,
            "allow": 1,
            "methods": "GET|POST|PUT|PATCH|DELETE"
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

        $response = $this->sendRequest('/backend/scope/100', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Could not find scope"
}
JSON;

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/scope/' . $this->id, 'POST', array(
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
        $response = $this->sendRequest('/backend/scope/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'   => 'Test',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Scope successful updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'name')
            ->from('fusio_scope')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals('Test', $row['name']);
    }

    public function testDelete()
    {
        // delete all scope references to successful delete an scope
        Environment::getService('connection')->executeUpdate('DELETE FROM fusio_app_scope WHERE scope_id = :scope_id', ['scope_id' => $this->id]);
        Environment::getService('connection')->executeUpdate('DELETE FROM fusio_user_scope WHERE scope_id = :scope_id', ['scope_id' => $this->id]);

        $response = $this->sendRequest('/backend/scope/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Scope successful deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id')
            ->from('fusio_scope')
            ->where('id = :id')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => $this->id]);

        $this->assertEmpty($row);
    }

    public function testDeleteAppScopeAssigned()
    {
        Environment::getService('connection')->executeUpdate('DELETE FROM fusio_user_scope WHERE scope_id = :scope_id', ['scope_id' => $this->id]);

        $response = $this->sendRequest('/backend/scope/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(409, $response->getStatusCode(), $body);
        $this->assertTrue(strpos($body, 'Scope is assigned to an app') !== false, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id')
            ->from('fusio_scope')
            ->where('id = :id')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => $this->id]);

        $this->assertNotEmpty($row);
    }

    public function testDeleteUserScopeAssigned()
    {
        Environment::getService('connection')->executeUpdate('DELETE FROM fusio_app_scope WHERE scope_id = :scope_id', ['scope_id' => $this->id]);

        $response = $this->sendRequest('/backend/scope/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(409, $response->getStatusCode(), $body);
        $this->assertTrue(strpos($body, 'Scope is assigned to an user') !== false, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id')
            ->from('fusio_scope')
            ->where('id = :id')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => $this->id]);

        $this->assertNotEmpty($row);
    }
}

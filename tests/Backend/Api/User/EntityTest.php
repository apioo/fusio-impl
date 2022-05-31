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

use Fusio\Impl\Table\User;
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
    private $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = Fixture::getId('fusio_user', 'Consumer');
    }

    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/user/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/entity.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/user/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = preg_replace('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/m', '[datetime]', $body);

        $expect = <<<'JSON'
{
    "id": 2,
    "roleId": 3,
    "provider": 1,
    "status": 1,
    "name": "Consumer",
    "email": "consumer@localhost.com",
    "points": 100,
    "scopes": [
        "consumer",
        "consumer.app",
        "consumer.event",
        "consumer.grant",
        "consumer.log",
        "consumer.page",
        "consumer.payment",
        "consumer.plan",
        "consumer.scope",
        "consumer.subscription",
        "consumer.transaction",
        "consumer.user",
        "authorization",
        "foo",
        "bar"
    ],
    "apps": [
        {
            "id": 5,
            "status": 3,
            "name": "Deactivated",
            "url": "http:\/\/google.com",
            "appKey": "f46af464-f7eb-4d04-8661-13063a30826b",
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 2,
            "name": "Pending",
            "url": "http:\/\/google.com",
            "appKey": "7c14809c-544b-43bd-9002-23e1c2de6067",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "status": 1,
            "name": "Foo-App",
            "url": "http:\/\/google.com",
            "appKey": "5347307d-d801-4075-9aaa-a21a29a448c5",
            "date": "[datetime]"
        }
    ],
    "date": "[datetime]"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/backend/user/10', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Could not find user"
}
JSON;

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/user/' . $this->id, 'POST', array(
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
        $response = $this->sendRequest('/backend/user/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'roleId' => 2,
            'planId' => 2,
            'status' => User::STATUS_ACTIVE,
            'name'   => 'bar',
            'email'  => 'bar@bar.com',
            'scopes' => ['bar'],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "User successfully updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'role_id', 'plan_id', 'status', 'name', 'email')
            ->from('fusio_user')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(2, $row['role_id']);
        $this->assertEquals(2, $row['plan_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('bar', $row['name']);
        $this->assertEquals('bar@bar.com', $row['email']);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('user_id', 'scope_id')
            ->from('fusio_user_scope')
            ->where('user_id = :user_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $scopes = Environment::getService('connection')->fetchAll($sql, ['user_id' => $this->id]);

        $this->assertEquals(1, count($scopes));
        $this->assertEquals(2, $scopes[0]['user_id']);
        $this->assertEquals(42, $scopes[0]['scope_id']);
    }

    public function testPutAttributes()
    {
        $response = $this->sendRequest('/backend/user/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status' => 1,
            'name'   => 'bar',
            'email'  => 'bar@bar.com',
            'scopes' => ['bar'],
            'attributes' => [
                'first_name' => 'Foo',
                'last_name' => 'Bar',
            ],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "User successfully updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'name', 'email')
            ->from('fusio_user')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(1, $row['status']);
        $this->assertEquals('bar', $row['name']);
        $this->assertEquals('bar@bar.com', $row['email']);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('user_id', 'scope_id')
            ->from('fusio_user_scope')
            ->where('user_id = :user_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $scopes = Environment::getService('connection')->fetchAll($sql, ['user_id' => $this->id]);

        $this->assertEquals(1, count($scopes));
        $this->assertEquals(2, $scopes[0]['user_id']);
        $this->assertEquals(42, $scopes[0]['scope_id']);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('name', 'value')
            ->from('fusio_user_attribute')
            ->where('user_id = ' . $this->id)
            ->orderBy('id', 'DESC')
            ->getSQL();

        $attributes = Environment::getService('connection')->fetchAll($sql);

        $this->assertEquals([[
            'name'  => 'last_name',
            'value' => 'Bar',
        ], [
            'name'  => 'first_name',
            'value' => 'Foo',
        ]], $attributes);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/user/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "User successfully deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_user')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(User::STATUS_DELETED, $row['status']);
    }
}

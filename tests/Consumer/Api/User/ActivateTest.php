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

namespace Fusio\Impl\Tests\Consumer\Api\User;

use Firebase\JWT\JWT;
use Fusio\Model\Consumer\User_Register;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * ActivateTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ActivateTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/consumer/activate', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/activate.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/activate', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $register = new User_Register();
        $register->setName('baz');
        $register->setEmail('baz@localhost.com');
        $register->setPassword('test1234!');
        Environment::getService('user_register_service')->register($register);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'provider', 'status', 'remote_id', 'name', 'email')
            ->from('fusio_user')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();
        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(6, $row['id']);
        $this->assertEquals(1, $row['provider']);
        $this->assertEquals(2, $row['status']);
        $this->assertEquals('', $row['remote_id']);
        $this->assertEquals('baz', $row['name']);
        $this->assertEquals('baz@localhost.com', $row['email']);

        $token = $this->connection->fetchColumn('SELECT token FROM fusio_user WHERE id = :id', ['id' => 6]);

        $response = $this->sendRequest('/consumer/activate', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'token' => $token,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals(true, $data->success);
        $this->assertEquals('Activation successful', $data->message);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('provider', 'status', 'remote_id', 'name', 'email')
            ->from('fusio_user')
            ->where('id = :id')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();
        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => 6]);

        $this->assertEquals(1, $row['provider']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('', $row['remote_id']);
        $this->assertEquals('baz', $row['name']);
        $this->assertEquals('baz@localhost.com', $row['email']);
    }

    public function testPostExpiredToken()
    {
        $payload = [
            'jit' => 'foo',
            'exp' => time() - 60,
        ];

        $token = JWT::encode($payload, Environment::getService('config')->get('fusio_project_key'));

        $response = $this->sendRequest('/consumer/activate', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'token' => $token,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals(false, $data->success);
        $this->assertEquals('Invalid token provided', substr($data->message, 0, 22));
    }

    public function testPostInvalidUserId()
    {
        $payload = [
            'jit' => 'foo',
            'exp' => time() + 60,
        ];

        $token = JWT::encode($payload, Environment::getService('config')->get('fusio_project_key'));

        $response = $this->sendRequest('/consumer/activate', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'token' => $token,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals(false, $data->success);
        $this->assertEquals('Could not find user', substr($data->message, 0, 19));
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/activate', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/activate', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

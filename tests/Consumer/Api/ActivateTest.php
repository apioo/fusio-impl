<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Tests\Consumer;

use Firebase\JWT\JWT;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * ActivateTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ActivateTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/activate', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        Environment::getService('consumer_service')->register('baz', 'baz@localhost.com', 'test1234!', null);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'provider', 'status', 'remoteId', 'name', 'email')
            ->from('fusio_user')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();
        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(6, $row['id']);
        $this->assertEquals(1, $row['provider']);
        $this->assertEquals(2, $row['status']);
        $this->assertEquals('', $row['remoteId']);
        $this->assertEquals('baz', $row['name']);
        $this->assertEquals('baz@localhost.com', $row['email']);

        $payload = [
            'sub' => 6,
            'exp' => time() + (60 * 60),
        ];

        $token = JWT::encode($payload, Environment::getService('config')->get('fusio_project_key'));

        $response = $this->sendRequest('http://127.0.0.1/consumer/activate', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
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
            ->select('provider', 'status', 'remoteId', 'name', 'email')
            ->from('fusio_user')
            ->where('id = :id')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();
        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => 6]);

        $this->assertEquals(1, $row['provider']);
        $this->assertEquals(0, $row['status']);
        $this->assertEquals('', $row['remoteId']);
        $this->assertEquals('baz', $row['name']);
        $this->assertEquals('baz@localhost.com', $row['email']);
    }

    public function testPostExpiredToken()
    {
        $payload = [
            'sub' => 6,
            'exp' => time() - 60,
        ];

        $token = JWT::encode($payload, Environment::getService('config')->get('fusio_project_key'));

        $response = $this->sendRequest('http://127.0.0.1/consumer/activate', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'token' => $token,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertEquals(500, $response->getStatusCode(), $body);
        $this->assertEquals(false, $data->success);
        $this->assertEquals('Expired token', substr($data->message, 0, 13));
    }

    public function testPostInvalidUserId()
    {
        $payload = [
            'sub' => 16,
            'exp' => time() + 60,
        ];

        $token = JWT::encode($payload, Environment::getService('config')->get('fusio_project_key'));

        $response = $this->sendRequest('http://127.0.0.1/consumer/activate', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'token' => $token,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertEquals(false, $data->success);
        $this->assertEquals('Could not find user', substr($data->message, 0, 19));
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/activate', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/activate', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Tests\Consumer\Api\User;

use Firebase\JWT\JWT;
use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Service\User\Register;
use Fusio\Impl\Tests\Fixture;
use Fusio\Model\Consumer\UserRegister;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * ActivateTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ActivateTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/activate', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $register = new UserRegister();
        $register->setName('baz');
        $register->setEmail('baz@localhost.com');
        $register->setPassword('test1234!');
        Environment::getService(Register::class)->register($register);

        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'provider', 'status', 'remote_id', 'name', 'email')
            ->from('fusio_user')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();
        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(6, $row['id']);
        $this->assertEquals(1, $row['provider']);
        $this->assertEquals(2, $row['status']);
        $this->assertEquals('', $row['remote_id']);
        $this->assertEquals('baz', $row['name']);
        $this->assertEquals('baz@localhost.com', $row['email']);

        $token = $this->connection->fetchOne('SELECT token FROM fusio_user WHERE id = :id', ['id' => 6]);

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
        $sql = $this->connection->createQueryBuilder()
            ->select('provider', 'status', 'remote_id', 'name', 'email')
            ->from('fusio_user')
            ->where('id = :id')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();
        $row = $this->connection->fetchAssociative($sql, ['id' => 6]);

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

        $jsonWebToken = Environment::getService(JsonWebToken::class);
        $token = $jsonWebToken->encode($payload);

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

        $jsonWebToken = Environment::getService(JsonWebToken::class);
        $token = $jsonWebToken->encode($payload);

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

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/activate', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

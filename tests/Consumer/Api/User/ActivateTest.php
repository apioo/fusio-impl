<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Service\System\ContextFactory;
use Fusio\Impl\Service\User\Register;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Model\Consumer\UserRegister;
use PSX\Framework\Test\Environment;

/**
 * ActivateTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ActivateTest extends DbTestCase
{
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
        $context = Environment::getService(ContextFactory::class)->newAnonymousContext();
        $userId = Environment::getService(Register::class)->register($register, $context);

        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'identity_id', 'status', 'remote_id', 'name', 'email')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();
        $row = $this->connection->fetchAssociative($sql, ['id' => $userId]);

        $this->assertEquals($userId, $row['id']);
        $this->assertEquals(null, $row['identity_id']);
        $this->assertEquals(2, $row['status']);
        $this->assertEquals('', $row['remote_id']);
        $this->assertEquals('baz', $row['name']);
        $this->assertEquals('baz@localhost.com', $row['email']);

        $token = $this->connection->fetchOne('SELECT token FROM fusio_user WHERE id = :id', ['id' => $userId]);

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
            ->select('identity_id', 'status', 'remote_id', 'name', 'email')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();
        $row = $this->connection->fetchAssociative($sql, ['id' => $userId]);

        $this->assertEquals(null, $row['identity_id']);
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

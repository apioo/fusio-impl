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

use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * LoginTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class LoginTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/login', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/login', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'username' => 'Consumer',
            'password' => 'qf2vX10Ec3wFZHx0K1eL',
        ]));

        $body  = (string) $response->getBody();
        $data  = json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);

        $jsonWebToken = Environment::getService(JsonWebToken::class);
        $token = $jsonWebToken->decode($data->token);

        $this->assertNotEmpty($token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertEquals('Consumer', $token->name);

        // check database access token
        $sql = $this->connection->createQueryBuilder()
            ->select('app_id', 'user_id', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_token')
            ->where('token = :token')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['token' => $data->token]);

        $this->assertEquals(null, $row['app_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals('2a11f995-1306-5494-aaa5-51c74d882e07', $token->sub);
        $this->assertEquals('consumer,consumer.account,consumer.app,consumer.event,consumer.grant,consumer.identity,consumer.log,consumer.page,consumer.payment,consumer.plan,consumer.scope,consumer.token,consumer.transaction,consumer.webhook,authorization,foo,bar', $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);
    }

    public function testPostWithScopes()
    {
        $response = $this->sendRequest('/consumer/login', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'username' => 'Consumer',
            'password' => 'qf2vX10Ec3wFZHx0K1eL',
            'scopes'   => ['foo', 'bar', 'baz', 'backend']
        ]));

        $body  = (string) $response->getBody();
        $data  = json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);

        $jsonWebToken = Environment::getService(JsonWebToken::class);
        $token = $jsonWebToken->decode($data->token);

        $this->assertNotEmpty($token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertEquals('Consumer', $token->name);

        // check database access token
        $sql = $this->connection->createQueryBuilder()
            ->select('app_id', 'user_id', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_token')
            ->where('token = :token')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['token' => $data->token]);

        $this->assertEquals(null, $row['app_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals('2a11f995-1306-5494-aaa5-51c74d882e07', $token->sub);
        $this->assertEquals('foo,bar', $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);
    }

    public function testPostInvalidCredentials()
    {
        $response = $this->sendRequest('/consumer/login', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'username' => 'Consumer',
            'password' => 'foo',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Invalid name or password', substr($data['message'], 0, 24), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/login', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'refresh_token' => 'b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873',
        ]));

        $body  = (string) $response->getBody();
        $data  = json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);

        $jsonWebToken = Environment::getService(JsonWebToken::class);
        $token = $jsonWebToken->decode($data->token);

        $this->assertNotEmpty($token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertEquals('Consumer', $token->name);

        // check database access token
        $sql = $this->connection->createQueryBuilder()
            ->select('app_id', 'user_id', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_token')
            ->where('token = :token')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['token' => $data->token]);

        $this->assertEquals(2, $row['app_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals('2a11f995-1306-5494-aaa5-51c74d882e07', $token->sub);
        $this->assertEquals('consumer', $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);
    }

    public function testPutInvalidRefreshToken()
    {
        $response = $this->sendRequest('/consumer/login', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'refresh_token' => 'foobar',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Invalid refresh token', substr($data['message'], 0, 21), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/login', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

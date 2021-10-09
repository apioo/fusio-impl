<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * LoginTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class LoginTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/consumer/login', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/login.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/login', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
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

        $token = JWT::decode($data->token, Environment::getConfig()->get('fusio_project_key'), ['HS256']);

        $this->assertNotEmpty($token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertEquals('Consumer', $token->name);

        // check database access token
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('app_id', 'user_id', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_app_token')
            ->where('token = :token')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['token' => $data->token]);

        $this->assertEquals(2, $row['app_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals('2a11f995-1306-5494-aaa5-51c74d882e07', $token->sub);
        $this->assertEquals('consumer,consumer.app,consumer.event,consumer.grant,consumer.page,consumer.plan,consumer.scope,consumer.subscription,consumer.transaction,consumer.user,authorization,foo,bar', $row['scope']);
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

        $token = JWT::decode($data->token, Environment::getConfig()->get('fusio_project_key'), ['HS256']);

        $this->assertNotEmpty($token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertEquals('Consumer', $token->name);

        // check database access token
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('app_id', 'user_id', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_app_token')
            ->where('token = :token')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['token' => $data->token]);

        $this->assertEquals(2, $row['app_id']);
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

        $token = JWT::decode($data->token, Environment::getConfig()->get('fusio_project_key'), ['HS256']);

        $this->assertNotEmpty($token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertEquals('Consumer', $token->name);

        // check database access token
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('app_id', 'user_id', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_app_token')
            ->where('token = :token')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['token' => $data->token]);

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

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

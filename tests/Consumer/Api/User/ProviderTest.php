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

namespace Fusio\Impl\Tests\Consumer\Api\User;

use Firebase\JWT\JWT;
use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Table\User;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Client\Client;

/**
 * ProviderTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ProviderTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/consumer/provider/github', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/provider.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/provider/github', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPostFacebook()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a', 'token_type' => 'bearer', 'expires_in' => time() + 60])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 1, 'name' => 'octocat', 'email' => 'octocat@github.com'])),
        ]);

        $container = [];
        $history   = Middleware::history($container);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);

        $id = $this->connection->fetchOne('SELECT id FROM fusio_config WHERE name = :name', ['name' => 'provider_facebook_secret']);
        Environment::getContainer()->set('http_client', $client);
        Environment::getService('connection')->update('fusio_config', ['value' => 'facebook'], ['id' => $id]);

        $response = $this->sendRequest('/consumer/provider/facebook', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'code' => 'foo',
            'clientId' => 'bar',
            'redirectUri' => 'http://google.com',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertToken($data->token, ProviderInterface::PROVIDER_FACEBOOK);

        $this->assertEquals(2, count($container));
        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('https://graph.facebook.com/v12.0/oauth/access_token?client_id=bar&redirect_uri=' . urlencode('http://google.com') . '&client_secret=facebook&code=foo', $transaction['request']->getUri());

        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('https://graph.facebook.com/v2.5/me?access_token=e72e16c7e42f292c6912e7710c838347ae178b4a&fields=id%2Cname%2Cemail', $transaction['request']->getUri());
        $this->assertEquals(['Bearer e72e16c7e42f292c6912e7710c838347ae178b4a'], $transaction['request']->getHeader('Authorization'));
    }

    public function testPostGithub()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a', 'scope' => 'user,gist', 'token_type' => 'bearer'])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 1, 'login' => 'octocat', 'email' => 'octocat@github.com'])),
        ]);

        $container = [];
        $history   = Middleware::history($container);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);

        $id = $this->connection->fetchOne('SELECT id FROM fusio_config WHERE name = :name', ['name' => 'provider_github_secret']);
        Environment::getContainer()->set('http_client', $client);
        Environment::getService('connection')->update('fusio_config', ['value' => 'github'], ['id' => $id]);

        $response = $this->sendRequest('/consumer/provider/github', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'code' => 'foo',
            'clientId' => 'bar',
            'redirectUri' => 'http://google.com',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertToken($data->token, ProviderInterface::PROVIDER_GITHUB);

        $this->assertEquals(2, count($container));
        $transaction = array_shift($container);

        $this->assertEquals('POST', $transaction['request']->getMethod());
        $this->assertEquals('https://github.com/login/oauth/access_token', $transaction['request']->getUri());
        $this->assertEquals('code=foo&client_id=bar&client_secret=github&redirect_uri=http%3A%2F%2Fgoogle.com', (string) $transaction['request']->getBody());

        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('https://api.github.com/user', $transaction['request']->getUri());
        $this->assertEquals(['Bearer e72e16c7e42f292c6912e7710c838347ae178b4a'], $transaction['request']->getHeader('Authorization'));
    }

    public function testPostGoogle()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a'])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 1, 'name' => 'octocat', 'email' => 'octocat@github.com'])),
        ]);

        $container = [];
        $history   = Middleware::history($container);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);

        $id = $this->connection->fetchOne('SELECT id FROM fusio_config WHERE name = :name', ['name' => 'provider_google_secret']);
        Environment::getContainer()->set('http_client', $client);
        Environment::getService('connection')->update('fusio_config', ['value' => 'google'], ['id' => $id]);

        $response = $this->sendRequest('/consumer/provider/google', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'code' => 'foo',
            'clientId' => 'bar',
            'redirectUri' => 'http://google.com',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertToken($data->token, ProviderInterface::PROVIDER_GOOGLE);

        $this->assertEquals(2, count($container));
        $transaction = array_shift($container);

        $this->assertEquals('POST', $transaction['request']->getMethod());
        $this->assertEquals('https://oauth2.googleapis.com/token', $transaction['request']->getUri());
        $this->assertEquals('code=foo&client_id=bar&client_secret=google&redirect_uri=http%3A%2F%2Fgoogle.com&grant_type=authorization_code', (string) $transaction['request']->getBody());

        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('https://www.googleapis.com/userinfo/v2/me', $transaction['request']->getUri());
        $this->assertEquals(['Bearer e72e16c7e42f292c6912e7710c838347ae178b4a'], $transaction['request']->getHeader('Authorization'));
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/provider/github', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/provider/github', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
    
    protected function assertToken($jwt, $provider)
    {
        $token = JWT::decode($jwt, Environment::getConfig()->get('fusio_project_key'), ['HS256']);

        $this->assertNotEmpty($token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertEquals('octocat', $token->name);

        // check database access token
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('app_id', 'user_id', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_app_token')
            ->where('token = :token')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['token' => $jwt]);

        $this->assertEquals(2, $row['app_id']);
        $this->assertEquals(6, $row['user_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals('eb9a3e30-9c88-5525-b229-903113421324', $token->sub);
        $this->assertEquals('consumer,consumer.app,consumer.event,consumer.grant,consumer.log,consumer.page,consumer.payment,consumer.plan,consumer.scope,consumer.subscription,consumer.transaction,consumer.user,authorization', $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);

        // check new user
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('status', 'provider', 'remote_id', 'name', 'email', 'password')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => $row['user_id']]);

        $this->assertEquals(User::STATUS_ACTIVE, $row['status']);
        $this->assertEquals($provider, $row['provider']);
        $this->assertEquals('1', $row['remote_id']);
        $this->assertEquals('octocat', $row['name']);
        $this->assertEquals('octocat@github.com', $row['email']);
        $this->assertEquals(null, $row['password']);
    }
}

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
use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Provider;
use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Table\User;
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
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ProviderTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/provider/github', 'GET', [
            'User-Agent' => 'Fusio TestCase',
        ]);

        $body = (string)$response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPostFacebook()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a', 'token_type' => 'bearer', 'expires_in' => time() + 60])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 1, 'name' => 'octocat', 'email' => 'octocat@github.com'])),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);
        Environment::getService(Provider\User\Facebook::class)->setHttpClient($client);

        $this->connection->update('fusio_config', ['value' => 'facebook'], ['name' => 'provider_facebook_secret']);

        $response = $this->sendRequest('/consumer/provider/facebook', 'POST', [
            'User-Agent' => 'Fusio TestCase',
        ], json_encode([
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
        $this->assertEquals('https://graph.facebook.com/v12.0/oauth/access_token?code=foo&client_id=bar&client_secret=facebook&redirect_uri=' . urlencode('http://google.com'), (string) $transaction['request']->getUri());

        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('https://graph.facebook.com/v2.5/me?access_token=e72e16c7e42f292c6912e7710c838347ae178b4a&fields=id%2Cname%2Cemail', (string) $transaction['request']->getUri());
        $this->assertEquals(['Bearer e72e16c7e42f292c6912e7710c838347ae178b4a'], $transaction['request']->getHeader('Authorization'));
    }

    public function testPostGithub()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a', 'scope' => 'user,gist', 'token_type' => 'bearer'])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 1, 'login' => 'octocat', 'email' => 'octocat@github.com'])),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);
        Environment::getService(Provider\User\Github::class)->setHttpClient($client);

        $this->connection->update('fusio_config', ['value' => 'github'], ['name' => 'provider_github_secret']);

        $response = $this->sendRequest('/consumer/provider/github', 'POST', [
            'User-Agent' => 'Fusio TestCase',
        ], json_encode([
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
        $this->assertEquals('https://github.com/login/oauth/access_token', (string) $transaction['request']->getUri());
        $this->assertEquals('code=foo&client_id=bar&client_secret=github&redirect_uri=http%3A%2F%2Fgoogle.com', (string) $transaction['request']->getBody());

        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('https://api.github.com/user', (string) $transaction['request']->getUri());
        $this->assertEquals(['Bearer e72e16c7e42f292c6912e7710c838347ae178b4a'], $transaction['request']->getHeader('Authorization'));
    }

    public function testPostGoogle()
    {
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a'])),
            new Response(200, ['Content-Type' => 'application/json'], json_encode(['id' => 1, 'name' => 'octocat', 'email' => 'octocat@github.com'])),
        ]);

        $container = [];
        $history = Middleware::history($container);

        $handler = HandlerStack::create($mock);
        $handler->push($history);

        $client = new Client(['handler' => $handler]);
        Environment::getService(Provider\User\Google::class)->setHttpClient($client);

        $this->connection->update('fusio_config', ['value' => 'google'], ['name' => 'provider_google_secret']);

        $response = $this->sendRequest('/consumer/provider/google', 'POST', [
            'User-Agent' => 'Fusio TestCase',
        ], json_encode([
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
        $this->assertEquals('https://oauth2.googleapis.com/token', (string) $transaction['request']->getUri());
        $this->assertEquals('code=foo&client_id=bar&client_secret=google&redirect_uri=http%3A%2F%2Fgoogle.com&grant_type=authorization_code', (string) $transaction['request']->getBody());

        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('https://www.googleapis.com/userinfo/v2/me', (string) $transaction['request']->getUri());
        $this->assertEquals(['Bearer e72e16c7e42f292c6912e7710c838347ae178b4a'], $transaction['request']->getHeader('Authorization'));
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/provider/github', 'PUT', [
            'User-Agent' => 'Fusio TestCase',
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/provider/github', 'DELETE', [
            'User-Agent' => 'Fusio TestCase',
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string)$response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    protected function assertToken(string $jwt, string $provider): void
    {
        $jsonWebToken = Environment::getService(JsonWebToken::class);
        $token = $jsonWebToken->decode($jwt);

        $this->assertNotEmpty($token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertEquals('octocat', $token->name);

        // check database access token
        $sql = $this->connection->createQueryBuilder()
            ->select('app_id', 'user_id', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_app_token')
            ->where('token = :token')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['token' => $jwt]);

        $this->assertEquals(2, $row['app_id']);
        $this->assertEquals(6, $row['user_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals('eb9a3e30-9c88-5525-b229-903113421324', $token->sub);
        $this->assertEquals('consumer,consumer.account,consumer.app,consumer.event,consumer.grant,consumer.log,consumer.page,consumer.payment,consumer.plan,consumer.scope,consumer.subscription,consumer.transaction,authorization,default', $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);

        // check new user
        $sql = $this->connection->createQueryBuilder()
            ->select('status', 'provider', 'remote_id', 'name', 'email', 'password')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => $row['user_id']]);

        $this->assertEquals(User::STATUS_ACTIVE, $row['status']);
        $this->assertEquals($provider, $row['provider']);
        $this->assertEquals('1', $row['remote_id']);
        $this->assertEquals('octocat', $row['name']);
        $this->assertEquals('octocat@github.com', $row['email']);
        $this->assertEquals(null, $row['password']);
    }
}

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

namespace Fusio\Impl\Tests\Consumer\Api\Identity;

use Fusio\Impl\Provider\Identity\Facebook;
use Fusio\Impl\Provider\Identity\Github;
use Fusio\Impl\Provider\Identity\Google;
use Fusio\Impl\Provider\Identity\OpenIDConnect;
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
use PSX\Uri\Url;

/**
 * ExchangeTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ExchangeTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGetFacebook()
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
        Environment::getService(Facebook::class)->setHttpClient($client);

        $response = $this->sendRequest('/consumer/identity/1/exchange?code=foobar&state=facebook-state', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(302, $response->getStatusCode(), $body);

        $url = Url::parse($response->getHeader('Location'));
        $parameters = $url->getParameters();

        $this->assertNotEmpty($parameters['access_token'], $url->toString());
        $this->assertToken($parameters['access_token'], 1);
        $this->assertEquals('bearer', $parameters['token_type'], $url->toString());
        $this->assertNotEmpty($parameters['expires_in'], $url->toString());

        $this->assertEquals(2, count($container));
        $transaction = array_shift($container);

        $this->assertEquals('POST', $transaction['request']->getMethod());
        $this->assertEquals('https://graph.facebook.com/v12.0/oauth/access_token', (string) $transaction['request']->getUri());
        $this->assertEquals('grant_type=authorization_code&code=foobar&client_id=facebook-key&client_secret=facebook-secret&redirect_uri=http%3A%2F%2F127.0.0.1%2Fconsumer%2Fidentity%2F1%2Fexchange', (string) $transaction['request']->getBody());

        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('https://graph.facebook.com/v2.5/me?fields=id%2Cname%2Cemail', (string) $transaction['request']->getUri());
        $this->assertEquals(['Bearer e72e16c7e42f292c6912e7710c838347ae178b4a'], $transaction['request']->getHeader('Authorization'));
    }

    public function testGetGithub()
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
        Environment::getService(Github::class)->setHttpClient($client);

        $response = $this->sendRequest('/consumer/identity/2/exchange?code=foobar&state=github-state', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(302, $response->getStatusCode(), $body);

        $url = Url::parse($response->getHeader('Location'));
        $parameters = $url->getParameters();

        $this->assertNotEmpty($parameters['access_token'], $url->toString());
        $this->assertToken($parameters['access_token'], 2);
        $this->assertEquals('bearer', $parameters['token_type'], $url->toString());
        $this->assertNotEmpty($parameters['expires_in'], $url->toString());

        $this->assertEquals(2, count($container));
        $transaction = array_shift($container);

        $this->assertEquals('POST', $transaction['request']->getMethod());
        $this->assertEquals('https://github.com/login/oauth/access_token', (string) $transaction['request']->getUri());
        $this->assertEquals('grant_type=authorization_code&code=foobar&client_id=github-key&client_secret=github-secret&redirect_uri=http%3A%2F%2F127.0.0.1%2Fconsumer%2Fidentity%2F2%2Fexchange', (string) $transaction['request']->getBody());

        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('https://api.github.com/user', (string) $transaction['request']->getUri());
        $this->assertEquals(['Bearer e72e16c7e42f292c6912e7710c838347ae178b4a'], $transaction['request']->getHeader('Authorization'));
    }

    public function testGetGoogle()
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
        Environment::getService(Google::class)->setHttpClient($client);

        $response = $this->sendRequest('/consumer/identity/3/exchange?code=foobar&state=google-state', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(302, $response->getStatusCode(), $body);

        $url = Url::parse($response->getHeader('Location'));
        $parameters = $url->getParameters();

        $this->assertNotEmpty($parameters['access_token'], $url->toString());
        $this->assertToken($parameters['access_token'], 3);
        $this->assertEquals('bearer', $parameters['token_type'], $url->toString());
        $this->assertNotEmpty($parameters['expires_in'], $url->toString());

        $this->assertEquals(2, count($container));
        $transaction = array_shift($container);

        $this->assertEquals('POST', $transaction['request']->getMethod());
        $this->assertEquals('https://oauth2.googleapis.com/token', (string) $transaction['request']->getUri());
        $this->assertEquals('grant_type=authorization_code&code=foobar&client_id=google-key&client_secret=google-secret&redirect_uri=http%3A%2F%2F127.0.0.1%2Fconsumer%2Fidentity%2F3%2Fexchange', (string) $transaction['request']->getBody());

        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('https://openidconnect.googleapis.com/v1/userinfo', (string) $transaction['request']->getUri());
        $this->assertEquals(['Bearer e72e16c7e42f292c6912e7710c838347ae178b4a'], $transaction['request']->getHeader('Authorization'));
    }

    public function testGetOpenIDConnect()
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
        Environment::getService(OpenIDConnect::class)->setHttpClient($client);

        $response = $this->sendRequest('/consumer/identity/4/exchange?code=foobar&state=openid-state', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(302, $response->getStatusCode(), $body);

        $url = Url::parse($response->getHeader('Location'));
        $parameters = $url->getParameters();

        $this->assertNotEmpty($parameters['access_token'], $url->toString());
        $this->assertToken($parameters['access_token'], 4);
        $this->assertEquals('bearer', $parameters['token_type'], $url->toString());
        $this->assertNotEmpty($parameters['expires_in'], $url->toString());

        $this->assertEquals(2, count($container));
        $transaction = array_shift($container);

        $this->assertEquals('POST', $transaction['request']->getMethod());
        $this->assertEquals('http://127.0.0.1/authorization/token', (string) $transaction['request']->getUri());
        $this->assertEquals('grant_type=authorization_code&code=foobar&client_id=openid-key&client_secret=openid-secret&redirect_uri=http%3A%2F%2F127.0.0.1%2Fconsumer%2Fidentity%2F4%2Fexchange', (string) $transaction['request']->getBody());

        $transaction = array_shift($container);

        $this->assertEquals('GET', $transaction['request']->getMethod());
        $this->assertEquals('http://127.0.0.1/authorization/whoami', (string) $transaction['request']->getUri());
        $this->assertEquals(['Bearer e72e16c7e42f292c6912e7710c838347ae178b4a'], $transaction['request']->getHeader('Authorization'));
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/identity/1/exchange', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/identity/1/exchange', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/identity/1/exchange', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    protected function assertToken(string $jwt, int $identityId): void
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
            ->from('fusio_token')
            ->where('token = :token')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['token' => $jwt]);

        $this->assertEquals(2, $row['app_id']);
        $this->assertEquals(6, $row['user_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals('eb9a3e30-9c88-5525-b229-903113421324', $token->sub);
        $this->assertEquals('consumer,consumer.account,consumer.app,consumer.event,consumer.grant,consumer.identity,consumer.log,consumer.page,consumer.payment,consumer.plan,consumer.scope,consumer.subscription,consumer.transaction,authorization,default', $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);

        // check new user
        $sql = $this->connection->createQueryBuilder()
            ->select('status', 'identity_id', 'remote_id', 'name', 'email', 'password')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => $row['user_id']]);

        $this->assertEquals(User::STATUS_ACTIVE, $row['status']);
        $this->assertEquals($identityId, $row['identity_id']);
        $this->assertEquals('1', $row['remote_id']);
        $this->assertEquals('octocat', $row['name']);
        $this->assertEquals('octocat@github.com', $row['email']);
        $this->assertEquals(null, $row['password']);
    }
}

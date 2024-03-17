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

namespace Fusio\Impl\Tests\Consumer\Api\Authorize;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * AuthorizeTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AuthorizeTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/consumer/authorize?client_id=5347307d-d801-4075-9aaa-a21a29a448c5&scope=backend,foo,bar', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "id": 3,
    "name": "Foo-App",
    "url": "http:\/\/google.com",
    "scopes": [
        {
            "id": 48,
            "name": "foo",
            "description": "Foo access"
        },
        {
            "id": 49,
            "name": "bar",
            "description": "Bar access"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('/ the following properties are required: responseType, clientId, scope, allow', substr($data['message'], 0, 77), $body);
    }

    public function testPostCode()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'code',
            'clientId' => '5347307d-d801-4075-9aaa-a21a29a448c5',
            'redirectUri' => 'http://google.com',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => true,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertArrayHasKey('type', $data, $body);
        $this->assertArrayHasKey('code', $data, $body);
        $this->assertArrayHasKey('redirectUri', $data, $body);
        $this->assertEquals('code', $data['type'], $body);
        $this->assertNotEmpty($data['code'], $body);
        $this->assertEquals('http://google.com?code=' . urlencode($data['code']) . '&state=state', $data['redirectUri'], $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('app_id', 'user_id', 'code', 'redirect_uri', 'scope')
            ->from('fusio_app_code')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(3, $row['app_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals($data['code'], $row['code']);
        $this->assertEquals('http://google.com', $row['redirect_uri']);
        // its important that we can not obtain a backend scope
        $this->assertEquals('authorization,foo,bar', $row['scope']);
    }

    public function testPostCodeWithoutRedirectUri()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'code',
            'clientId' => '5347307d-d801-4075-9aaa-a21a29a448c5',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => true,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertArrayHasKey('type', $data, $body);
        $this->assertArrayHasKey('code', $data, $body);
        $this->assertArrayHasKey('redirectUri', $data, $body);
        $this->assertEquals('code', $data['type'], $body);
        $this->assertNotEmpty($data['code'], $body);
        $this->assertEquals('#', $data['redirectUri'], $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('app_id', 'user_id', 'code', 'redirect_uri', 'scope')
            ->from('fusio_app_code')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(3, $row['app_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals($data['code'], $row['code']);
        $this->assertNull($row['redirect_uri']);
        // its important that we can not obtain a backend scope
        $this->assertEquals('authorization,foo,bar', $row['scope']);
    }

    public function testPostCodeDisallow()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'code',
            'clientId' => '5347307d-d801-4075-9aaa-a21a29a448c5',
            'redirectUri' => 'http://google.com',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => false,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertArrayHasKey('type', $data, $body);
        $this->assertArrayHasKey('redirectUri', $data, $body);
        $this->assertEquals('access_denied', $data['type'], $body);
        $this->assertEquals('http://google.com?error=access_denied&state=state', $data['redirectUri'], $body);
    }

    public function testPostToken()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'token',
            'clientId' => '5347307d-d801-4075-9aaa-a21a29a448c5',
            'redirectUri' => 'http://google.com',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => true,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertArrayHasKey('type', $data, $body);
        $this->assertArrayHasKey('token', $data, $body);
        $this->assertArrayHasKey('redirectUri', $data, $body);
        $this->assertEquals('token', $data['type'], $body);
        $this->assertTrue(is_array($data['token']), $body);
        $this->assertNotEmpty($data['token']['access_token'], $body);
        $this->assertEquals('bearer', $data['token']['token_type'], $body);
        $this->assertEquals(172800, $data['token']['expires_in'], $body);
        $this->assertEquals('authorization,foo,bar', $data['token']['scope'], $body);

        // add state parameter which should be added by the server if available
        $data['token']['state'] = 'state';

        $this->assertEquals('http://google.com#' . http_build_query($data['token']), $data['redirectUri'], $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('app_id', 'user_id', 'status', 'token', 'scope', 'expire')
            ->from('fusio_token')
            ->where('token = :token')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, [
            'token' => $data['token']['access_token']
        ]);

        $this->assertEquals(3, $row['app_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals($data['token']['access_token'], $row['token']);
        $this->assertEquals('authorization,foo,bar', $row['scope']);
        $this->assertContains($row['expire'], $this->getExpireTimes(false), $body);
    }

    public function testPostTokenWithoutRedirectUri()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'token',
            'clientId' => '5347307d-d801-4075-9aaa-a21a29a448c5',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => false,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertArrayHasKey('type', $data, $body);
        $this->assertArrayHasKey('redirectUri', $data, $body);
        $this->assertEquals('access_denied', $data['type'], $body);
        $this->assertEquals('#', $data['redirectUri'], $body);
    }

    public function testPostTokenDisallow()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'token',
            'clientId' => '5347307d-d801-4075-9aaa-a21a29a448c5',
            'redirectUri' => 'http://google.com',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => false,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertArrayHasKey('type', $data, $body);
        $this->assertArrayHasKey('redirectUri', $data, $body);
        $this->assertEquals('access_denied', $data['type'], $body);
        $this->assertEquals('http://google.com#error=access_denied&state=state', $data['redirectUri'], $body);
    }

    public function testPostInvalidResponseType()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'foo',
            'clientId' => '5347307d-d801-4075-9aaa-a21a29a448c5',
            'redirectUri' => 'http://google.com',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => false,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Invalid response type', substr($data['message'], 0, 21), $body);
    }

    public function testPostInvalidClient()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'code',
            'clientId' => 'a347307d-d801-4075-9aaa-a21a29a448c5',
            'redirectUri' => 'http://google.com',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => false,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Unknown client id', substr($data['message'], 0, 17), $body);
    }

    public function testPostInvalidRedirectUri()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'code',
            'clientId' => '5347307d-d801-4075-9aaa-a21a29a448c5',
            'redirectUri' => 'foo',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => false,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Redirect uri must be an absolute url', substr($data['message'], 0, 36), $body);
    }

    public function testPostInvalidScheme()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'code',
            'clientId' => '5347307d-d801-4075-9aaa-a21a29a448c5',
            'redirectUri' => 'foo://google.com',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => false,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Invalid redirect uri scheme', substr($data['message'], 0, 27), $body);
    }

    public function testPostInvalidHost()
    {
        $response = $this->sendRequest('/consumer/authorize', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'responseType' => 'code',
            'clientId' => '5347307d-d801-4075-9aaa-a21a29a448c5',
            'redirectUri' => 'http://yahoo.com',
            'scope' => 'bar,backend,authorization,foo',
            'state' => 'state',
            'allow' => false,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertEquals('Redirect uri must have the same host as the app url', substr($data['message'], 0, 51), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/authorize', 'PUT', array(
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
        $response = $this->sendRequest('/consumer/authorize', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    private function getExpireTimes($timestamps = true)
    {
        $expireTime = strtotime('+2 days');
        $timeRange  = [$expireTime - 1, $expireTime];

        if (!$timestamps) {
            $timeRange = array_map(function ($value) {
                return date('Y-m-d H:i:s', $value);
            }, $timeRange);
        }

        return $timeRange;
    }
}

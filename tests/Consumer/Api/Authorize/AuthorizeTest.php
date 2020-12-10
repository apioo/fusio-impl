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

namespace Fusio\Impl\Tests\Consumer\Api\Authorize;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * AuthorizeTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AuthorizeTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/consumer/authorize', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/authorize.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
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
            "id": 34,
            "name": "foo",
            "description": "Foo access"
        },
        {
            "id": 35,
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

        $this->assertEquals(500, $response->getStatusCode(), $body);
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
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('app_id', 'user_id', 'code', 'redirect_uri', 'scope')
            ->from('fusio_app_code')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(3, $row['app_id']);
        $this->assertEquals(1, $row['user_id']);
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
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('app_id', 'user_id', 'code', 'redirect_uri', 'scope')
            ->from('fusio_app_code')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(3, $row['app_id']);
        $this->assertEquals(1, $row['user_id']);
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
        $this->assertContains($data['token']['expires_in'], $this->getExpireTimes(), $body);
        $this->assertEquals('authorization,foo,bar', $data['token']['scope'], $body);

        // add state parameter which should be added by the server if available
        $data['token']['state'] = 'state';

        $this->assertEquals('http://google.com#' . http_build_query($data['token']), $data['redirectUri'], $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('app_id', 'user_id', 'status', 'token', 'scope', 'expire')
            ->from('fusio_app_token')
            ->where('token = :token')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, [
            'token' => $data['token']['access_token']
        ]);

        $this->assertEquals(3, $row['app_id']);
        $this->assertEquals(1, $row['user_id']);
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

        $this->assertEquals(405, $response->getStatusCode(), $body);
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

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    private function getExpireTimes($timestamps = true)
    {
        $expireTime = strtotime('+1 hour');
        $timeRange  = [$expireTime - 1, $expireTime];

        if (!$timestamps) {
            $timeRange = array_map(function ($value) {
                return date('Y-m-d H:i:s', $value);
            }, $timeRange);
        }

        return $timeRange;
    }
}

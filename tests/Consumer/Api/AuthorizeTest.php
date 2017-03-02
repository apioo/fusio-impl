<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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
        $response = $this->sendRequest('http://127.0.0.1/doc/*/consumer/authorize', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/consumer\/authorize",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "Request": {
                "type": "object",
                "title": "request",
                "properties": {
                    "responseType": {
                        "type": "string"
                    },
                    "clientId": {
                        "type": "string"
                    },
                    "redirectUri": {
                        "type": "string"
                    },
                    "scope": {
                        "type": "string"
                    },
                    "state": {
                        "type": "string"
                    },
                    "allow": {
                        "type": "boolean"
                    }
                },
                "required": [
                    "responseType",
                    "clientId",
                    "scope",
                    "allow"
                ]
            },
            "Token": {
                "type": "object",
                "title": "token",
                "properties": {
                    "access_token": {
                        "type": "string"
                    },
                    "token_type": {
                        "type": "string"
                    },
                    "expires_in": {
                        "type": "string"
                    },
                    "scope": {
                        "type": "string"
                    }
                }
            },
            "Response": {
                "type": "object",
                "title": "response",
                "properties": {
                    "type": {
                        "type": "string"
                    },
                    "token": {
                        "$ref": "#\/definitions\/Token"
                    },
                    "code": {
                        "type": "string"
                    },
                    "redirectUri": {
                        "type": "string"
                    }
                }
            },
            "POST-request": {
                "$ref": "#\/definitions\/Request"
            },
            "POST-200-response": {
                "$ref": "#\/definitions\/Response"
            }
        }
    },
    "methods": {
        "POST": {
            "request": "#\/definitions\/POST-request",
            "responses": {
                "200": "#\/definitions\/POST-200-response"
            }
        }
    },
    "links": [
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/consumer\/authorize"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/consumer\/authorize"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
            ->select('appId', 'userId', 'code', 'redirectUri', 'scope')
            ->from('fusio_app_code')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(3, $row['appId']);
        $this->assertEquals(1, $row['userId']);
        $this->assertEquals($data['code'], $row['code']);
        $this->assertEquals('http://google.com', $row['redirectUri']);
        // its important that we can not obtain a backend scope
        $this->assertEquals('authorization,foo,bar', $row['scope']);
    }

    public function testPostCodeWithoutRedirectUri()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
            ->select('appId', 'userId', 'code', 'redirectUri', 'scope')
            ->from('fusio_app_code')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(3, $row['appId']);
        $this->assertEquals(1, $row['userId']);
        $this->assertEquals($data['code'], $row['code']);
        $this->assertNull($row['redirectUri']);
        // its important that we can not obtain a backend scope
        $this->assertEquals('authorization,foo,bar', $row['scope']);
    }

    public function testPostCodeDisallow()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
            ->select('appId', 'userId', 'status', 'token', 'scope', 'expire')
            ->from('fusio_app_token')
            ->where('token = :token')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, [
            'token' => $data['token']['access_token']
        ]);

        $this->assertEquals(3, $row['appId']);
        $this->assertEquals(1, $row['userId']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals($data['token']['access_token'], $row['token']);
        $this->assertEquals('authorization,foo,bar', $row['scope']);
        $this->assertContains($row['expire'], $this->getExpireTimes(false), $body);
    }

    public function testPostTokenWithoutRedirectUri()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'POST', array(
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
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'PUT', array(
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
        $response = $this->sendRequest('http://127.0.0.1/consumer/authorize', 'DELETE', array(
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
            $timeRange = array_map(function($value) {
                return date('Y-m-d H:i:s', $value);
            }, $timeRange);
        }

        return $timeRange;
    }
}

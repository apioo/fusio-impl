<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Consumer\User;

use Firebase\JWT\JWT;
use Fusio\Impl\Service\User\ProviderInterface;
use Fusio\Impl\Table\User;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Handler\Mock;
use PSX\Http\RequestInterface;

/**
 * ProviderTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ProviderTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/doc/*/consumer/provider/github', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "path": "\/consumer\/provider\/:provider",
    "version": "*",
    "status": 1,
    "description": "",
    "schema": {
        "$schema": "http:\/\/json-schema.org\/draft-04\/schema#",
        "id": "urn:schema.phpsx.org#",
        "definitions": {
            "Provider": {
                "type": "object",
                "title": "provider",
                "properties": {
                    "code": {
                        "type": "string"
                    },
                    "clientId": {
                        "type": "string"
                    },
                    "redirectUri": {
                        "type": "string"
                    }
                },
                "additionalProperties": true
            },
            "Jwt": {
                "type": "object",
                "title": "jwt",
                "properties": {
                    "token": {
                        "type": "string"
                    }
                }
            },
            "POST-request": {
                "$ref": "#\/definitions\/Provider"
            },
            "POST-200-response": {
                "$ref": "#\/definitions\/Jwt"
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
            "rel": "openapi",
            "href": "\/export\/openapi\/*\/consumer\/provider\/:provider"
        },
        {
            "rel": "swagger",
            "href": "\/export\/swagger\/*\/consumer\/provider\/:provider"
        },
        {
            "rel": "raml",
            "href": "\/export\/raml\/*\/consumer\/provider\/:provider"
        }
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/github', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPostFacebook()
    {
        $handler = new Mock();
        $handler->add('GET', 'https://graph.facebook.com/v2.5/me?access_token=e72e16c7e42f292c6912e7710c838347ae178b4a&fields=id%2Cemail%2Cfirst_name%2Clast_name%2Clink%2Cname', function (RequestInterface $request) {
            $this->assertEquals('Bearer e72e16c7e42f292c6912e7710c838347ae178b4a', $request->getHeader('Authorization'));

            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['id' => 1, 'name' => 'octocat', 'email' => 'octocat@github.com']);

            return $response;
        });

        $handler->add('GET', 'https://graph.facebook.com/v2.5/oauth/access_token?client_id=bar&redirect_uri=' . urlencode('http://google.com') . '&client_secret=facebook&code=foo', function (RequestInterface $request) {
            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a', 'token_type' => 'bearer', 'expires_in' => time() + 60]);

            return $response;
        });

        Environment::getService('http_client')->setHandler($handler);
        Environment::getService('connection')->update('fusio_config', ['value' => 'facebook'], ['id' => 7]);

        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/facebook', 'POST', array(
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
    }

    public function testPostGithub()
    {
        $handler = new Mock();
        $handler->add('GET', 'https://api.github.com/user', function (RequestInterface $request) {
            $this->assertEquals('Bearer e72e16c7e42f292c6912e7710c838347ae178b4a', $request->getHeader('Authorization'));

            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['id' => 1, 'login' => 'octocat', 'email' => 'octocat@github.com']);

            return $response;
        });

        $handler->add('POST', 'https://github.com/login/oauth/access_token', function (RequestInterface $request) {
            $body = (string) $request->getBody();
            $this->assertEquals('code=foo&client_id=bar&client_secret=github&redirect_uri=http%3A%2F%2Fgoogle.com', $body);

            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a', 'scope' => 'user,gist', 'token_type' => 'bearer']);

            return $response;
        });

        Environment::getService('http_client')->setHandler($handler);
        Environment::getService('connection')->update('fusio_config', ['value' => 'github'], ['id' => 9]);

        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/github', 'POST', array(
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
    }

    public function testPostGoogle()
    {
        $handler = new Mock();
        $handler->add('GET', 'https://www.googleapis.com/plus/v1/people/me/openIdConnect', function (RequestInterface $request) {
            $this->assertEquals('Bearer e72e16c7e42f292c6912e7710c838347ae178b4a', $request->getHeader('Authorization'));

            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['sub' => 1, 'name' => 'octocat', 'email' => 'octocat@github.com']);

            return $response;
        });

        $handler->add('POST', 'https://accounts.google.com/o/oauth2/token', function (RequestInterface $request) {
            $body = (string) $request->getBody();
            $this->assertEquals('code=foo&client_id=bar&client_secret=google&redirect_uri=http%3A%2F%2Fgoogle.com&grant_type=authorization_code', $body);

            $response = 'HTTP/1.1 200 OK' . "\r\n";
            $response.= 'Content-Type: application/json' . "\r\n";
            $response.= "\r\n";
            $response.= json_encode(['access_token' => 'e72e16c7e42f292c6912e7710c838347ae178b4a']);

            return $response;
        });

        Environment::getService('http_client')->setHandler($handler);
        Environment::getService('connection')->update('fusio_config', ['value' => 'google'], ['id' => 8]);

        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/google', 'POST', array(
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
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/github', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('http://127.0.0.1/consumer/provider/github', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
    
    protected function assertToken($token, $provider)
    {
        $token = JWT::decode($token, Environment::getConfig()->get('fusio_project_key'), ['HS256']);

        $this->assertNotEmpty($token->sub);
        $this->assertNotEmpty($token->iat);
        $this->assertNotEmpty($token->exp);
        $this->assertEquals('octocat', $token->name);

        // check database access token
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('appId', 'userId', 'status', 'token', 'scope', 'ip', 'expire')
            ->from('fusio_app_token')
            ->where('token = :token')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['token' => $token->sub]);

        $this->assertEquals(2, $row['appId']);
        $this->assertEquals(6, $row['userId']);
        $this->assertEquals(1, $row['status']);
        $this->assertNotEmpty($row['token']);
        $this->assertEquals($row['token'], $token->sub);
        $this->assertEquals('authorization,consumer', $row['scope']);
        $this->assertEquals('127.0.0.1', $row['ip']);
        $this->assertNotEmpty($row['expire']);

        // check new user
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('status', 'provider', 'remoteId', 'name', 'email', 'password')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => $row['userId']]);

        $this->assertEquals(User::STATUS_CONSUMER, $row['status']);
        $this->assertEquals($provider, $row['provider']);
        $this->assertEquals('1', $row['remoteId']);
        $this->assertEquals('octocat', $row['name']);
        $this->assertEquals('octocat@github.com', $row['email']);
        $this->assertEquals(null, $row['password']);
    }
}

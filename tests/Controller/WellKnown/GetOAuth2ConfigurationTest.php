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

namespace Fusio\Impl\Tests\System\Api\WellKnown;

use Fusio\Impl\Tests\DbTestCase;

/**
 * GetOAuth2ConfigurationTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetOAuth2ConfigurationTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/.well-known/oauth-authorization-server', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $actual = (string) $response->getBody();
        $expect = <<<JSON
{
    "issuer": "http:\/\/127.0.0.1",
    "authorization_endpoint": "http:\/\/127.0.0.1\/authorization\/authorize",
    "token_endpoint": "http:\/\/127.0.0.1\/authorization\/token",
    "token_endpoint_auth_methods_supported": [
        "client_secret_basic"
    ],
    "token_endpoint_auth_signing_alg_values_supported": [
        "HS256"
    ],
    "userinfo_endpoint": "http:\/\/127.0.0.1\/authorization\/whoami",
    "scopes_supported": [
        "bar",
        "default",
        "foo",
        "plan_scope"
    ],
    "response_types_supported": [
        "code"
    ],
    "response_modes_supported": [
        "query"
    ],
    "grant_types_supported": [
        "authorization_code",
        "client_credentials",
        "password",
        "refresh_token"
    ],
    "service_documentation": "https:\/\/docs.fusio-project.org\/"
}
JSON;


        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/.well-known/oauth-authorization-server', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/.well-known/oauth-authorization-server', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/.well-known/oauth-authorization-server', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

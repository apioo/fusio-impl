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
 * GetOAuth2ProtectedResourceTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetOAuth2ProtectedResourceTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/.well-known/oauth-protected-resource', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $actual = (string) $response->getBody();
        $expect = <<<JSON
{
    "resource": "http:\/\/127.0.0.1",
    "authorization_servers": [
        "http:\/\/127.0.0.1"
    ],
    "scopes_supported": [
        "bar",
        "default",
        "foo",
        "plan_scope"
    ],
    "bearer_methods_supported": [
        "header"
    ],
    "resource_signing_alg_values_supported": [
        "HS256"
    ]
}
JSON;


        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGetResource()
    {
        $response = $this->sendRequest('/.well-known/oauth-protected-resource/foo/bar', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $actual = (string) $response->getBody();
        $expect = <<<JSON
{
    "resource": "http:\/\/127.0.0.1\/foo\/bar",
    "authorization_servers": [
        "http:\/\/127.0.0.1"
    ],
    "scopes_supported": [
        "bar",
        "default",
        "foo",
        "plan_scope"
    ],
    "bearer_methods_supported": [
        "header"
    ],
    "resource_signing_alg_values_supported": [
        "HS256"
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/.well-known/oauth-protected-resource', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/.well-known/oauth-protected-resource', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/.well-known/oauth-protected-resource', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

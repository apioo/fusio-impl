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

namespace Fusio\Impl\Tests\Controller;

use Fusio\Impl\Base;
use Fusio\Impl\Tests\DbTestCase;

/**
 * WelcomeTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class WelcomeTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $version = Base::getVersion();
        $body    = (string) $response->getBody();
        $expect  = <<<JSON
{
    "apiVersion": "{$version}",
    "title": "Fusio",
    "description": "Self-Hosted API Management for Builders.",
    "categories": [
        "authorization",
        "backend",
        "consumer",
        "default",
        "system"
    ],
    "paymentCurrency": "EUR",
    "scopes": [
        "bar",
        "default",
        "foo",
        "plan_scope"
    ],
    "links": [
        {
            "rel": "root",
            "href": "http:\/\/127.0.0.1\/"
        },
        {
            "rel": "openapi",
            "href": "http:\/\/127.0.0.1\/system\/generator\/spec-openapi"
        },
        {
            "rel": "typeapi",
            "href": "http:\/\/127.0.0.1\/system\/generator\/spec-typeapi"
        },
        {
            "rel": "route",
            "href": "http:\/\/127.0.0.1\/system\/route"
        },
        {
            "rel": "health",
            "href": "http:\/\/127.0.0.1\/system\/health"
        },
        {
            "rel": "oauth-authorization-server",
            "href": "http:\/\/127.0.0.1\/system\/oauth-authorization-server"
        },
        {
            "rel": "api-catalog",
            "href": "http:\/\/127.0.0.1\/system\/api-catalog"
        },
        {
            "rel": "oauth2",
            "href": "http:\/\/127.0.0.1\/authorization\/token"
        },
        {
            "rel": "whoami",
            "href": "http:\/\/127.0.0.1\/authorization\/whoami"
        },
        {
            "rel": "about",
            "href": "https:\/\/www.fusio-project.org"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testPut()
    {
        $response = $this->sendRequest('/', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testPatch()
    {
        $response = $this->sendRequest('/', 'PATCH', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $this->assertEquals(404, $response->getStatusCode());
    }

}

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

namespace Fusio\Impl\Tests\Controller;

use Fusio\Impl\Base;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * WelcomeTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class WelcomeTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

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
    "categories": [
        "authorization",
        "system",
        "consumer",
        "backend",
        "default"
    ],
    "paymentCurrency": "EUR",
    "scopes": [
        "bar",
        "default",
        "foo",
        "plan_scope"
    ],
    "apps": {
        "fusio": "http:\/\/127.0.0.1\/apps\/fusio"
    },
    "links": [
        {
            "rel": "root",
            "href": "http:\/\/127.0.0.1\/"
        },
        {
            "rel": "openapi",
            "href": "http:\/\/127.0.0.1\/system\/export\/openapi\/*\/*"
        },
        {
            "rel": "documentation",
            "href": "http:\/\/127.0.0.1\/system\/doc"
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
            "rel": "jsonrpc",
            "href": "http:\/\/127.0.0.1\/system\/jsonrpc"
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

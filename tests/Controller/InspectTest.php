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

namespace Fusio\Impl\Tests\Controller;

use Firebase\JWT\JWT;
use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Json\Parser;

/**
 * InspectTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class InspectTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/inspect/bar?foo=bar', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "method": "GET",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('http://127.0.0.1/inspect/bar?foo=bar', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "method": "POST",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/inspect/bar?foo=bar', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "method": "PUT",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPatch()
    {
        $response = $this->sendRequest('http://127.0.0.1/inspect/bar?foo=bar', 'PATCH', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "method": "PATCH",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('http://127.0.0.1/inspect/bar?foo=bar', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "method": "DELETE",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

}

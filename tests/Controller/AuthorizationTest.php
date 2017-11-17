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

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * AuthorizationTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AuthorizationTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testPublic()
    {
        $response = $this->sendRequest('/foo', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "itemsPerPage": 16,
    "startIndex": 0,
    "entry": [
        {
            "id": 2,
            "title": "bar",
            "content": "foo",
            "date": "2015-02-27 19:59:15"
        },
        {
            "id": 1,
            "title": "foo",
            "content": "bar",
            "date": "2015-02-27 19:59:15"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $header = json_encode($response->getHeaders(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "warning": [
        "199 PSX \"Resource is in development\""
    ],
    "x-ratelimit-limit": [
        "8"
    ],
    "x-ratelimit-remaining": [
        "8"
    ],
    "vary": [
        "Accept"
    ],
    "content-type": [
        "application\/json"
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $header, $header);
    }

    public function testPublicWithAuthorization()
    {
        $response = $this->sendRequest('/foo', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "itemsPerPage": 16,
    "startIndex": 0,
    "entry": [
        {
            "id": 2,
            "title": "bar",
            "content": "foo",
            "date": "2015-02-27 19:59:15"
        },
        {
            "id": 1,
            "title": "foo",
            "content": "bar",
            "date": "2015-02-27 19:59:15"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $header = json_encode($response->getHeaders(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "warning": [
        "199 PSX \"Resource is in development\""
    ],
    "x-ratelimit-limit": [
        "16"
    ],
    "x-ratelimit-remaining": [
        "16"
    ],
    "vary": [
        "Accept"
    ],
    "content-type": [
        "application\/json"
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $header, $header);
    }

    public function testPublicWithInvalidAuthorization()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/foo', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer 1234'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Invalid access token"
}
JSON;

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $header = json_encode($response->getHeaders(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "www-authenticate": [
        "Bearer realm=\"Fusio\""
    ],
    "vary": [
        "Accept"
    ],
    "content-type": [
        "application\/json"
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $header, $header);
    }

    public function testPublicWithEmptyAuthorization()
    {
        $response = $this->sendRequest('/foo', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => ''
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "itemsPerPage": 16,
    "startIndex": 0,
    "entry": [
        {
            "id": 2,
            "title": "bar",
            "content": "foo",
            "date": "2015-02-27 19:59:15"
        },
        {
            "id": 1,
            "title": "foo",
            "content": "bar",
            "date": "2015-02-27 19:59:15"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $header = json_encode($response->getHeaders(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "warning": [
        "199 PSX \"Resource is in development\""
    ],
    "x-ratelimit-limit": [
        "8"
    ],
    "x-ratelimit-remaining": [
        "8"
    ],
    "vary": [
        "Accept"
    ],
    "content-type": [
        "application\/json"
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $header, $header);
    }

    public function testNotPublic()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);
        
        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $response = $this->sendRequest('/foo', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), $body);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Missing authorization header"
}
JSON;

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $header = json_encode($response->getHeaders(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "www-authenticate": [
        "Bearer realm=\"Fusio\""
    ],
    "vary": [
        "Accept"
    ],
    "content-type": [
        "application\/json"
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $header, $header);
    }

    public function testNotPublicWithAuthorization()
    {
        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $response = $this->sendRequest('/foo', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ), $body);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Entry successful created",
    "id": "3"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $header = json_encode($response->getHeaders(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "warning": [
        "199 PSX \"Resource is in development\""
    ],
    "x-ratelimit-limit": [
        "16"
    ],
    "x-ratelimit-remaining": [
        "16"
    ],
    "vary": [
        "Accept"
    ],
    "content-type": [
        "application\/json"
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $header, $header);
    }

    public function testNotPublicWithInvalidAuthorization()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);
        
        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $response = $this->sendRequest('/foo', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer 1234'
        ), $body);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Invalid access token"
}
JSON;

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $header = json_encode($response->getHeaders(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "www-authenticate": [
        "Bearer realm=\"Fusio\""
    ],
    "vary": [
        "Accept"
    ],
    "content-type": [
        "application\/json"
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $header, $header);
    }

    public function testNotPublicWithEmptyAuthorization()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);
        
        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $response = $this->sendRequest('/foo', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => ''
        ), $body);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Missing authorization header"
}
JSON;

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $header = json_encode($response->getHeaders(), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "www-authenticate": [
        "Bearer realm=\"Fusio\""
    ],
    "vary": [
        "Accept"
    ],
    "content-type": [
        "application\/json"
    ]
}
JSON;

        $this->assertJsonStringEqualsJsonString($expect, $header, $header);
    }
}

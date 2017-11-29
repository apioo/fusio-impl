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

    /**
     * @dataProvider providerDebugStatus
     */
    public function testGet($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/inspect/bar?foo=bar', 'GET', array(
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

    /**
     * @dataProvider providerDebugStatus
     */
    public function testGetError($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/inspect/bar?throw=1', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(500, $response->getStatusCode(), $body);

        $data = json_decode($body);

        $this->assertFalse($data->success);
        $this->assertEquals('Foobar', substr($data->message, 0, 6));
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testGetChangeStatus($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $statuuus = [
            Resource::STATUS_DEVELOPMENT,
            Resource::STATUS_ACTIVE,
            Resource::STATUS_DEPRECATED,
            Resource::STATUS_CLOSED,
        ];

        foreach ($statuuus as $key => $status) {
            // update the route status
            $response = $this->sendRequest('/backend/routes/' . (Fixture::getLastRouteId() + 2), 'PUT', array(
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
            ), json_encode([
                'path'   => '/foo',
                'config' => [[
                    'version' => 1,
                    'status'  => $status,
                    'methods' => [
                        'GET' => [
                            'active'   => true,
                            'public'   => true,
                            'action'   => 4,
                            'response' => 1,
                        ],
                    ],
                ]],
            ]));

            $body   = (string) $response->getBody();
            $expect = <<<'JSON'
{
    "success": true,
    "message": "Routes successful updated"
}
JSON;

            $this->assertEquals(200, $response->getStatusCode(), $body);
            $this->assertJsonStringEqualsJsonString($expect, $body, $body);

            // send request
            $response = $this->sendRequest('/inspect/bar', 'GET', array(
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
            ));

            $body = (string) $response->getBody();

            if ($status === Resource::STATUS_CLOSED) {
                $data = Parser::decode($body);

                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                ];

                $this->assertEquals(410, $response->getStatusCode(), $body);
                $this->assertEquals($headers, $response->getHeaders(), $body);
                $this->assertEquals(false, $data->success, $body);
                $this->assertEquals($debug ? 'PSX\\Http\\Exception\\GoneException' : 'Internal Server Error', $data->title, $body);
                $this->assertEquals('Resource is not longer supported', substr($data->message, 0, 32), $body);
            } else {
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
    "parameters": []
}
JSON;

                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'x-ratelimit-limit' => ['720'],
                    'x-ratelimit-remaining' => [720 - $key],
                ];

                if ($status === Resource::STATUS_DEVELOPMENT) {
                    $headers['warning'] = ['199 PSX "Resource is in development"'];
                } elseif ($status === Resource::STATUS_DEPRECATED) {
                    $headers['warning'] = ['199 PSX "Resource is deprecated"'];
                }

                $this->assertEquals(200, $response->getStatusCode(), $body);
                $this->assertEquals($headers, $response->getHeaders(), $body);
                $this->assertJsonStringEqualsJsonString($expect, $body, $body);
            }
        }
    }


    /**
     * @dataProvider providerDebugStatus
     */
    public function testGetChangeStatusError($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $statuuus = [
            Resource::STATUS_DEVELOPMENT,
            Resource::STATUS_ACTIVE,
            Resource::STATUS_DEPRECATED,
            Resource::STATUS_CLOSED,
        ];

        foreach ($statuuus as $key => $status) {
            // update the route status
            $response = $this->sendRequest('/backend/routes/' . (Fixture::getLastRouteId() + 2), 'PUT', array(
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
            ), json_encode([
                'path'   => '/foo',
                'config' => [[
                    'version' => 1,
                    'status'  => $status,
                    'methods' => [
                        'GET' => [
                            'active'   => true,
                            'public'   => true,
                            'action'   => 4,
                            'response' => 1,
                        ],
                    ],
                ]],
            ]));

            $body   = (string) $response->getBody();
            $expect = <<<'JSON'
{
    "success": true,
    "message": "Routes successful updated"
}
JSON;

            $this->assertEquals(200, $response->getStatusCode(), $body);
            $this->assertJsonStringEqualsJsonString($expect, $body, $body);

            // send request
            $response = $this->sendRequest('/inspect/bar?throw=1', 'GET', array(
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
            ));

            $body = (string) $response->getBody();

            if ($status === Resource::STATUS_CLOSED) {
                $data = Parser::decode($body);

                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                ];

                $this->assertEquals(410, $response->getStatusCode(), $body);
                $this->assertEquals($headers, $response->getHeaders(), $body);
                $this->assertEquals(false, $data->success, $body);
                $this->assertEquals($debug ? 'PSX\\Http\\Exception\\GoneException' : 'Internal Server Error', $data->title, $body);
                $this->assertEquals('Resource is not longer supported', substr($data->message, 0, 32), $body);
            } else {
                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'x-ratelimit-limit' => ['720'],
                    'x-ratelimit-remaining' => [720 - $key],
                ];

                if ($status === Resource::STATUS_DEVELOPMENT) {
                    $headers['warning'] = ['199 PSX "Resource is in development"'];
                } elseif ($status === Resource::STATUS_DEPRECATED) {
                    $headers['warning'] = ['199 PSX "Resource is deprecated"'];
                }

                $this->assertEquals(500, $response->getStatusCode(), $body);
                $this->assertEquals($headers, $response->getHeaders(), $body);

                $data = json_decode($body);

                $this->assertFalse($data->success);
                $this->assertEquals('Foobar', substr($data->message, 0, 6));
            }
        }
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testPost($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/inspect/bar?foo=bar', 'POST', array(
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

    /**
     * @dataProvider providerDebugStatus
     */
    public function testPut($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/inspect/bar?foo=bar', 'PUT', array(
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

    /**
     * @dataProvider providerDebugStatus
     */
    public function testPatch($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/inspect/bar?foo=bar', 'PATCH', array(
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

    /**
     * @dataProvider providerDebugStatus
     */
    public function testDelete($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/inspect/bar?foo=bar', 'DELETE', array(
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

    public function providerDebugStatus()
    {
        return [
            [true],
            [false],
        ];
    }
}

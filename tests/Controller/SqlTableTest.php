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
 * SqlTable
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SqlTable extends ControllerDbTestCase
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

        $headers = [
            'vary' => ['Accept'],
            'content-type' => ['application/json'],
            'warning' => ['199 PSX "Resource is in development"'],
            'x-ratelimit-limit' => ['16'],
            'x-ratelimit-remaining' => ['16'],
        ];

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals($headers, $response->getHeaders(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
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
            $response = $this->sendRequest('/backend/routes/' . (Fixture::getLastRouteId() + 1), 'PUT', array(
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
                            'action'   => 3,
                            'response' => 2,
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
            $response = $this->sendRequest('/foo', 'GET', array(
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

                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'x-ratelimit-limit' => ['16'],
                    'x-ratelimit-remaining' => [16 - $key],
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
    public function testPost($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

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

        $headers = [
            'vary' => ['Accept'],
            'content-type' => ['application/json'],
            'warning' => ['199 PSX "Resource is in development"'],
            'x-ratelimit-limit' => ['16'],
            'x-ratelimit-remaining' => ['16'],
        ];

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertEquals($headers, $response->getHeaders(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testGetAccessTokenJWT($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $key  = Environment::getContainer()->get('config')->get('fusio_project_key');
        $data = ['sub' => 'b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'];
        $jwt  = JWT::encode($data, $key);

        $response = $this->sendRequest('/foo', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer ' . $jwt
        ), $body);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Entry successful created",
    "id": "3"
}
JSON;

        $headers = [
            'vary' => ['Accept'],
            'content-type' => ['application/json'],
            'warning' => ['199 PSX "Resource is in development"'],
            'x-ratelimit-limit' => ['16'],
            'x-ratelimit-remaining' => ['16'],
        ];

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertEquals($headers, $response->getHeaders(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testRateLimit($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = null;
        for ($i = 0; $i < 10; $i++) {
            $response = $this->sendRequest('/foo', 'GET', array(
                'User-Agent' => 'Fusio TestCase'
            ));

            $body = (string) $response->getBody();
            $data = Parser::decode($body);

            if ($i < 8) {
                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'warning' => ['199 PSX "Resource is in development"'],
                    'x-ratelimit-limit' => ['8'],
                    'x-ratelimit-remaining' => [8 - $i],
                ];

                $this->assertEquals(200, $response->getStatusCode(), $body);
                $this->assertEquals($headers, $response->getHeaders(), $body);
            } else {
                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'warning' => ['199 PSX "Resource is in development"'],
                    'x-ratelimit-limit' => ['8'],
                    'x-ratelimit-remaining' => ['0'],
                ];

                $this->assertEquals(429, $response->getStatusCode(), $body);
                $this->assertEquals($headers, $response->getHeaders(), $body);
                $this->assertEquals(false, $data->success, $body);
                $this->assertEquals('Rate limit exceeded', substr($data->message, 0, 19), $body);
            }
        }
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testRateLimitAuthenticated($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = null;
        for ($i = 0; $i < 18; $i++) {
            $response = $this->sendRequest('/foo', 'GET', array(
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
            ));

            $body = (string) $response->getBody();
            $data = Parser::decode($body);

            if ($i < 16) {
                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'warning' => ['199 PSX "Resource is in development"'],
                    'x-ratelimit-limit' => ['16'],
                    'x-ratelimit-remaining' => [16 - $i],
                ];

                $this->assertEquals(200, $response->getStatusCode(), $body);
                $this->assertEquals($headers, $response->getHeaders(), $body);
            } else {
                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'warning' => ['199 PSX "Resource is in development"'],
                    'x-ratelimit-limit' => ['16'],
                    'x-ratelimit-remaining' => ['0'],
                ];

                $this->assertEquals(429, $response->getStatusCode(), $body);
                $this->assertEquals($headers, $response->getHeaders(), $body);
                $this->assertEquals(false, $data->success, $body);
                $this->assertEquals('Rate limit exceeded', substr($data->message, 0, 19), $body);
            }
        }
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testPut($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $response = $this->sendRequest('/foo', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ), $body);

        $body = (string) $response->getBody();
        $data = Parser::decode($body);

        $headers = [
            'vary' => ['Accept'],
            'content-type' => ['application/json'],
            'allow' => ['OPTIONS, HEAD, GET, POST'],
        ];

        $this->assertEquals(405, $response->getStatusCode(), $body);
        $this->assertEquals($headers, $response->getHeaders(), $body);
        $this->assertEquals(false, $data->success, $body);
        $this->assertEquals('Given request method is not supported', substr($data->message, 0, 37), $body);
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testHead($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/foo', 'HEAD', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ));

        $body = (string) $response->getBody();

        $headers = [
            'warning' => ['199 PSX "Resource is in development"'],
            'x-ratelimit-limit' => ['16'],
            'x-ratelimit-remaining' => ['16'],
            'vary' => ['Accept'],
            'content-type' => ['application/json'],
            'content-length' => ['379'],
        ];

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals($headers, $response->getHeaders(), $body);
        $this->assertEmpty($body);
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testOptions($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        $response = $this->sendRequest('/foo', 'OPTIONS', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $headers = [
            'warning' => ['199 PSX "Resource is in development"'],
            'x-ratelimit-limit' => ['8'],
            'x-ratelimit-remaining' => ['8'],
            'allow' => ['OPTIONS, HEAD, GET, POST'],
        ];

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals($headers, $response->getHeaders(), $body);
        $this->assertEmpty($body);
    }

    /**
     * @dataProvider providerDebugStatus
     */
    public function testOptionsPreflight($debug)
    {
        Environment::getContainer()->get('config')->set('psx_debug', $debug);

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = Environment::getService('connection');
        $connection->executeUpdate('UPDATE fusio_config SET value = :origin WHERE name = :name', [
            'origin' => '*',
            'name'   => 'cors_allow_origin',
        ]);

        $response = $this->sendRequest('/foo', 'OPTIONS', array(
            'User-Agent'    => 'Fusio TestCase',
            'Access-Control-Request-Method' => 'DELETE',
        ));

        $body = (string) $response->getBody();

        $headers = [
            'warning' => ['199 PSX "Resource is in development"'],
            'x-ratelimit-limit' => ['8'],
            'x-ratelimit-remaining' => ['8'],
            'allow' => ['OPTIONS, HEAD, GET, POST'],
            'access-control-allow-methods' => ['OPTIONS, HEAD, GET, POST'],
            'access-control-allow-origin' => ['*'],
        ];

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals($headers, $response->getHeaders(), $body);
        $this->assertEmpty($body);
    }

    public function providerDebugStatus()
    {
        return [
            [true],
            [false],
        ];
    }
}

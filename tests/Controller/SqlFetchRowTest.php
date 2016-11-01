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

namespace Fusio\Impl\Tests\Controller;

use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Json\Parser;

/**
 * SqlFetchRowTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SqlFetchRowTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/foo', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-02-27T19:59:15Z"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
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

        foreach ($statuuus as $status) {
            // update the route status
            $response = $this->sendRequest('http://127.0.0.1/backend/routes/63', 'PUT', array(
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
            $response = $this->sendRequest('http://127.0.0.1/foo', 'GET', array(
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
            ));

            $body = (string) $response->getBody();

            if ($status === Resource::STATUS_CLOSED) {
                $data = Parser::decode($body);

                $this->assertEquals(410, $response->getStatusCode(), $body);
                $this->assertEquals(false, $data->success, $body);
                $this->assertEquals($debug ? 'PSX\\Http\\Exception\\GoneException' : 'Internal Server Error', $data->title, $body);
                $this->assertEquals('Resource is not longer supported', substr($data->message, 0, 32), $body);
            } else {
                $expect = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-02-27T19:59:15Z"
}
JSON;

                $this->assertEquals(200, $response->getStatusCode(), $body);
                $this->assertJsonStringEqualsJsonString($expect, $body, $body);
            }
        }
    }

    public function providerDebugStatus()
    {
        return [
            [false],
            [true],
        ];
    }

    public function testPost()
    {
        $body = <<<'JSON'
{
    "title": "foo",
    "content": "bar",
    "date": "2015-07-04T13:03:00Z"
}
JSON;

        $response = $this->sendRequest('http://127.0.0.1/foo', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ), $body);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "id": "1",
    "title": "foo",
    "content": "bar",
    "date": "2015-02-27 19:59:15"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    /**
     * @depends testGet
     * @depends testPost
     */
    public function testRateLimit()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = null;
        for ($i = 0; $i < 8; $i++) {
            $response = $this->sendRequest('http://127.0.0.1/foo', 'GET', array(
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
            ));
        }

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Rate limit exceeded"
}
JSON;

        $this->assertEquals(429, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

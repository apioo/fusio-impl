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

use Fusio\Impl\Tests\Fixture;
use PSX\Api\OperationInterface;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Json\Parser;

/**
 * SqlTableTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class SqlTableTest extends ControllerDbTestCase
{
    private ?int $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = Fixture::getId('fusio_operation', 'test.listFoo');
    }

    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
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
            "date": "2015-02-27T19:59:15+00:00"
        },
        {
            "id": 1,
            "title": "foo",
            "content": "bar",
            "date": "2015-02-27T19:59:15+00:00"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Remaining'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetChangeStatus()
    {
        $stabilities = [
            OperationInterface::STABILITY_DEPRECATED,
            OperationInterface::STABILITY_EXPERIMENTAL,
            OperationInterface::STABILITY_STABLE,
            OperationInterface::STABILITY_LEGACY,
        ];

        foreach ($stabilities as $key => $stability) {
            // update the operation status
            $response = $this->sendRequest('/backend/operation/' . $this->id, 'PUT', array(
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
            ), json_encode([
                'stability' => $stability,
            ]));

            $body   = (string) $response->getBody();
            $expect = <<<'JSON'
{
    "success": true,
    "message": "Operation successfully updated"
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
            "date": "2015-02-27T19:59:15+00:00"
        },
        {
            "id": 1,
            "title": "foo",
            "content": "bar",
            "date": "2015-02-27T19:59:15+00:00"
        }
    ]
}
JSON;

            if ($stability === OperationInterface::STABILITY_DEPRECATED) {
                $stabilityName = 'deprecated';
            } elseif ($stability === OperationInterface::STABILITY_EXPERIMENTAL) {
                $stabilityName = 'experimental';
            } elseif ($stability === OperationInterface::STABILITY_STABLE) {
                $stabilityName = 'stable';
            } elseif ($stability === OperationInterface::STABILITY_LEGACY) {
                $stabilityName = 'legacy';
            } else {
                throw new \RuntimeException('Provided an invalid stability');
            }

            $this->assertEquals(200, $response->getStatusCode(), $body);
            $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
            $this->assertEquals(8 - $key, $response->getHeader('RateLimit-Remaining'), $body);
            $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
            $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
            $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
            $this->assertEquals($stabilityName, $response->getHeader('X-Stability'), $body);
            $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
            $this->assertJsonStringEqualsJsonString($expect, $body, $body);
        }
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

        $response = $this->sendRequest('/foo', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ), $body);

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Entry successfully created",
    "id": 3,
    "affected": 1
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertEquals('16', $response->getHeader('RateLimit-Limit'), $body);
        $this->assertEquals('16', $response->getHeader('RateLimit-Remaining'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.createFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('stable', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testRateLimit()
    {
        for ($i = 0; $i < 10; $i++) {
            $response = $this->sendRequest('/foo', 'GET', array(
                'User-Agent' => 'Fusio TestCase'
            ));

            $body = (string) $response->getBody();
            $data = Parser::decode($body);

            if ($i < 8) {
                $this->assertEquals(200, $response->getStatusCode(), $body);
                $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
                $this->assertEquals(8 - $i, $response->getHeader('RateLimit-Remaining'), $body);
                $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
                $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
                $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
                $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
                $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
            } else {
                $this->assertEquals(429, $response->getStatusCode(), $body);
                $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
                $this->assertEquals('0', $response->getHeader('RateLimit-Remaining'), $body);
                $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
                $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
                $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
                $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
                $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
                $this->assertEquals(false, $data->success, $body);
                $this->assertEquals('Rate limit exceeded', substr($data->message, 0, 19), $body);
            }
        }
    }

    public function testRateLimitAuthenticated()
    {
        for ($i = 0; $i < 18; $i++) {
            $response = $this->sendRequest('/foo', 'GET', array(
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
            ));

            $body = (string) $response->getBody();
            $data = Parser::decode($body);

            if ($i < 8) {
                $this->assertEquals(200, $response->getStatusCode(), $body);
                $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
                $this->assertEquals(8 - $i, $response->getHeader('RateLimit-Remaining'), $body);
                $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
                $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
                $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
                $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
                $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
            } else {
                $this->assertEquals(429, $response->getStatusCode(), $body);
                $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
                $this->assertEquals('0', $response->getHeader('RateLimit-Remaining'), $body);
                $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
                $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
                $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
                $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
                $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
                $this->assertEquals(false, $data->success, $body);
                $this->assertEquals('Rate limit exceeded', substr($data->message, 0, 19), $body);
            }
        }
    }

    public function testCosts()
    {
        // check user points
        $points = $this->connection->fetchOne('SELECT points FROM fusio_user WHERE id = 4');
        $this->assertEquals(10, $points);

        for ($i = 0; $i < 15; $i++) {
            $response = $this->sendRequest('/foo', 'POST', array(
                'User-Agent'    => 'Fusio TestCase',
                'Authorization' => 'Bearer e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f',
                'Content-Type'  => 'application/json',
            ), \json_encode([
                'title' => 'foo',
                'content' => 'bar',
                'date' => date('Y-m-d\TH:i:s\Z'),
            ]));

            $body = (string) $response->getBody();
            $data = Parser::decode($body);

            if ($i < 10) {
                $this->assertEquals(201, $response->getStatusCode(), $body);
                $this->assertEquals('16', $response->getHeader('RateLimit-Limit'), $body);
                $this->assertEquals(16 - $i, $response->getHeader('RateLimit-Remaining'), $body);
                $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
                $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
                $this->assertEquals('test.createFoo', $response->getHeader('X-Operation-Id'), $body);
                $this->assertEquals('stable', $response->getHeader('X-Stability'), $body);
                $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);

                // check user points
                $points = $this->connection->fetchOne('SELECT points FROM fusio_user WHERE id = 4');
                $this->assertEquals(10 - ($i + 1), $points);
            } else {
                $this->assertEquals(429, $response->getStatusCode(), $body);
                $this->assertEquals('16', $response->getHeader('RateLimit-Limit'), $body);
                $this->assertEquals(16 - $i, $response->getHeader('RateLimit-Remaining'), $body);
                $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
                $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
                $this->assertEquals('test.createFoo', $response->getHeader('X-Operation-Id'), $body);
                $this->assertEquals('stable', $response->getHeader('X-Stability'), $body);
                $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
                $this->assertEquals(false, $data->success, $body);
                $this->assertEquals('Your account has not enough points to call this action. Please purchase new points in order to execute this action', substr($data->message, 0, 114), $body);

                // check user points
                $points = $this->connection->fetchOne('SELECT points FROM fusio_user WHERE id = 4');
                $this->assertEquals(0, $points);
            }
        }
    }

    public function testPut()
    {
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

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertEquals(false, $data->success, $body);
        $this->assertStringStartsWith('Unknown location', $data->message, $body);
    }

    public function testHead()
    {
        $response = $this->sendRequest('/foo', 'HEAD', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Remaining'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertGreaterThan(375, $response->getHeader('Content-Length'), $body);
        $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertEmpty($body);
    }

    public function testOptions()
    {
        $response = $this->sendRequest('/foo', 'OPTIONS', array(
            'User-Agent'    => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals('OPTIONS, HEAD, GET, POST', $response->getHeader('Allow'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertEmpty($body);
    }

    public function testCorsSimpleRequest()
    {
        $response = $this->sendRequest('/foo', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
            'Origin' => 'http://foo.example',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Limit'), $body);
        $this->assertEquals('8', $response->getHeader('RateLimit-Remaining'), $body);
        $this->assertEquals('*', $response->getHeader('Access-Control-Allow-Origin'), $body);
        $this->assertEquals('application/json', $response->getHeader('Content-Type'), $body);
        $this->assertRegExp('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $response->getHeader('X-Request-Id'), $body);
        $this->assertEquals('test.listFoo', $response->getHeader('X-Operation-Id'), $body);
        $this->assertEquals('experimental', $response->getHeader('X-Stability'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
    }

    public function testCorsPreflightedRequest()
    {
        $response = $this->sendRequest('/foo', 'OPTIONS', array(
            'User-Agent' => 'Fusio TestCase',
            'Origin' => 'http://foo.example',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type',
        ));

        $body = (string) $response->getBody();

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertEquals('OPTIONS, HEAD, GET, POST', $response->getHeader('Allow'), $body);
        $this->assertEquals('*', $response->getHeader('Access-Control-Allow-Origin'), $body);
        $this->assertEquals('OPTIONS, HEAD, GET, POST, PUT, DELETE, PATCH', $response->getHeader('Access-Control-Allow-Methods'), $body);
        $this->assertEquals('Accept, Accept-Language, Authorization, Content-Language, Content-Type', $response->getHeader('Access-Control-Allow-Headers'), $body);
        $this->assertEquals('*', $response->getHeader('Access-Control-Expose-Headers'), $body);
        $this->assertEquals('Fusio', $response->getHeader('X-Powered-By'), $body);
        $this->assertEmpty($body);
    }
}

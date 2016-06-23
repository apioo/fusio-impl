<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Tests\Backend\Api\Routes;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/routes', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 2,
    "startIndex": 0,
    "entry": [
        {
            "id": 56,
            "path": "\/foo"
        },
        {
            "id": 55,
            "path": "\/"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/routes', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path'   => '/bar',
            'config' => [[
                'version' => 1,
                'status'  => 4,
                'methods' => [
                    'GET' => [
                        'active'   => true,
                        'public'   => true,
                        'response' => 1,
                        'action'   => 3,
                    ],
                    'POST' => [
                        'active'   => true,
                        'public'   => true,
                        'request'  => 2,
                        'response' => 1,
                        'action'   => 3,
                    ]
                ],
            ]],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Route successful created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'methods', 'path', 'controller')
            ->from('fusio_routes')
            ->orderBy('id', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(1)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(57, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('GET|POST|PUT|DELETE', $row['methods']);
        $this->assertEquals('/bar', $row['path']);
        $this->assertEquals('Fusio\Impl\Controller\SchemaApiController', $row['controller']);

        // check methods
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'routeId', 'method', 'version', 'status', 'active', 'public', 'request', 'response', 'action')
            ->from('fusio_routes_method')
            ->where('routeId = :routeId')
            ->orderBy('id', 'ASC')
            ->setFirstResult(0)
            ->getSQL();

        $result = Environment::getService('connection')->fetchAll($sql, ['routeId' => $row['id']]);

        $this->assertEquals(2, count($result));

        $this->assertEquals('GET', $result[0]['method']);
        $this->assertEquals(1, $result[0]['version']);
        $this->assertEquals(4, $result[0]['status']);
        $this->assertEquals(1, $result[0]['active']);
        $this->assertEquals(1, $result[0]['public']);
        $this->assertEquals(null, $result[0]['request']);
        $this->assertEquals(3, $result[0]['action']);

        $this->assertEquals('POST', $result[1]['method']);
        $this->assertEquals(1, $result[1]['version']);
        $this->assertEquals(4, $result[1]['status']);
        $this->assertEquals(1, $result[1]['active']);
        $this->assertEquals(1, $result[1]['public']);
        $this->assertEquals(2, $result[1]['request']);
        $this->assertEquals(3, $result[1]['action']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/routes', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('http://127.0.0.1/backend/routes', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

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

namespace Fusio\Impl\Tests\Backend\Api\Scope;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * CategoriesTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class CategoriesTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/scope/categories', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/categories.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/scope/categories', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "categories": [
        {
            "id": 5,
            "name": "authorization",
            "scopes": [
                {
                    "id": 3,
                    "name": "authorization",
                    "description": ""
                }
            ]
        },
        {
            "id": 2,
            "name": "backend",
            "scopes": [
                {
                    "id": 1,
                    "name": "backend",
                    "description": ""
                },
                {
                    "id": 5,
                    "name": "backend.account",
                    "description": ""
                },
                {
                    "id": 6,
                    "name": "backend.action",
                    "description": ""
                },
                {
                    "id": 7,
                    "name": "backend.app",
                    "description": ""
                },
                {
                    "id": 8,
                    "name": "backend.audit",
                    "description": ""
                },
                {
                    "id": 9,
                    "name": "backend.category",
                    "description": ""
                },
                {
                    "id": 10,
                    "name": "backend.config",
                    "description": ""
                },
                {
                    "id": 11,
                    "name": "backend.connection",
                    "description": ""
                },
                {
                    "id": 12,
                    "name": "backend.cronjob",
                    "description": ""
                },
                {
                    "id": 13,
                    "name": "backend.dashboard",
                    "description": ""
                },
                {
                    "id": 14,
                    "name": "backend.event",
                    "description": ""
                },
                {
                    "id": 15,
                    "name": "backend.log",
                    "description": ""
                },
                {
                    "id": 16,
                    "name": "backend.marketplace",
                    "description": ""
                },
                {
                    "id": 17,
                    "name": "backend.page",
                    "description": ""
                },
                {
                    "id": 18,
                    "name": "backend.plan",
                    "description": ""
                },
                {
                    "id": 19,
                    "name": "backend.rate",
                    "description": ""
                },
                {
                    "id": 20,
                    "name": "backend.role",
                    "description": ""
                },
                {
                    "id": 21,
                    "name": "backend.route",
                    "description": ""
                },
                {
                    "id": 22,
                    "name": "backend.schema",
                    "description": ""
                },
                {
                    "id": 23,
                    "name": "backend.scope",
                    "description": ""
                },
                {
                    "id": 24,
                    "name": "backend.sdk",
                    "description": ""
                },
                {
                    "id": 25,
                    "name": "backend.statistic",
                    "description": ""
                },
                {
                    "id": 26,
                    "name": "backend.transaction",
                    "description": ""
                },
                {
                    "id": 27,
                    "name": "backend.user",
                    "description": ""
                }
            ]
        },
        {
            "id": 3,
            "name": "consumer",
            "scopes": [
                {
                    "id": 2,
                    "name": "consumer",
                    "description": ""
                },
                {
                    "id": 28,
                    "name": "consumer.app",
                    "description": ""
                },
                {
                    "id": 29,
                    "name": "consumer.event",
                    "description": ""
                },
                {
                    "id": 30,
                    "name": "consumer.grant",
                    "description": ""
                },
                {
                    "id": 31,
                    "name": "consumer.log",
                    "description": ""
                },
                {
                    "id": 32,
                    "name": "consumer.page",
                    "description": ""
                },
                {
                    "id": 33,
                    "name": "consumer.plan",
                    "description": ""
                },
                {
                    "id": 34,
                    "name": "consumer.scope",
                    "description": ""
                },
                {
                    "id": 35,
                    "name": "consumer.subscription",
                    "description": ""
                },
                {
                    "id": 36,
                    "name": "consumer.transaction",
                    "description": ""
                },
                {
                    "id": 37,
                    "name": "consumer.user",
                    "description": ""
                }
            ]
        },
        {
            "id": 1,
            "name": "default",
            "scopes": [
                {
                    "id": 40,
                    "name": "bar",
                    "description": "Bar access"
                },
                {
                    "id": 4,
                    "name": "default",
                    "description": ""
                },
                {
                    "id": 39,
                    "name": "foo",
                    "description": "Foo access"
                }
            ]
        },
        {
            "id": 4,
            "name": "system",
            "scopes": [
                {
                    "id": 38,
                    "name": "system",
                    "description": ""
                }
            ]
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/scope/categories', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/scope/categories', 'PUT', array(
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
        $response = $this->sendRequest('/backend/scope/categories', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

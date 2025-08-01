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

namespace Fusio\Impl\Tests\Backend\Api\Scope;

use Fusio\Impl\Tests\DbTestCase;

/**
 * CategoriesTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CategoriesTest extends DbTestCase
{
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
                    "id": 15,
                    "name": "backend.backup",
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
                    "id": 16,
                    "name": "backend.firewall",
                    "description": ""
                },
                {
                    "id": 17,
                    "name": "backend.form",
                    "description": ""
                },
                {
                    "id": 18,
                    "name": "backend.generator",
                    "description": ""
                },
                {
                    "id": 19,
                    "name": "backend.identity",
                    "description": ""
                },
                {
                    "id": 20,
                    "name": "backend.log",
                    "description": ""
                },
                {
                    "id": 21,
                    "name": "backend.marketplace",
                    "description": ""
                },
                {
                    "id": 26,
                    "name": "backend.operation",
                    "description": ""
                },
                {
                    "id": 22,
                    "name": "backend.page",
                    "description": ""
                },
                {
                    "id": 23,
                    "name": "backend.plan",
                    "description": ""
                },
                {
                    "id": 24,
                    "name": "backend.rate",
                    "description": ""
                },
                {
                    "id": 25,
                    "name": "backend.role",
                    "description": ""
                },
                {
                    "id": 27,
                    "name": "backend.schema",
                    "description": ""
                },
                {
                    "id": 28,
                    "name": "backend.scope",
                    "description": ""
                },
                {
                    "id": 29,
                    "name": "backend.sdk",
                    "description": ""
                },
                {
                    "id": 30,
                    "name": "backend.statistic",
                    "description": ""
                },
                {
                    "id": 31,
                    "name": "backend.tenant",
                    "description": ""
                },
                {
                    "id": 32,
                    "name": "backend.test",
                    "description": ""
                },
                {
                    "id": 33,
                    "name": "backend.token",
                    "description": ""
                },
                {
                    "id": 34,
                    "name": "backend.transaction",
                    "description": ""
                },
                {
                    "id": 35,
                    "name": "backend.trash",
                    "description": ""
                },
                {
                    "id": 36,
                    "name": "backend.user",
                    "description": ""
                },
                {
                    "id": 37,
                    "name": "backend.webhook",
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
                    "id": 50,
                    "name": "consumer.account",
                    "description": ""
                },
                {
                    "id": 38,
                    "name": "consumer.app",
                    "description": ""
                },
                {
                    "id": 39,
                    "name": "consumer.event",
                    "description": ""
                },
                {
                    "id": 43,
                    "name": "consumer.form",
                    "description": ""
                },
                {
                    "id": 40,
                    "name": "consumer.grant",
                    "description": ""
                },
                {
                    "id": 51,
                    "name": "consumer.identity",
                    "description": ""
                },
                {
                    "id": 41,
                    "name": "consumer.log",
                    "description": ""
                },
                {
                    "id": 42,
                    "name": "consumer.page",
                    "description": ""
                },
                {
                    "id": 44,
                    "name": "consumer.payment",
                    "description": ""
                },
                {
                    "id": 45,
                    "name": "consumer.plan",
                    "description": ""
                },
                {
                    "id": 46,
                    "name": "consumer.scope",
                    "description": ""
                },
                {
                    "id": 47,
                    "name": "consumer.token",
                    "description": ""
                },
                {
                    "id": 49,
                    "name": "consumer.transaction",
                    "description": ""
                },
                {
                    "id": 48,
                    "name": "consumer.webhook",
                    "description": ""
                }
            ]
        },
        {
            "id": 1,
            "name": "default",
            "scopes": [
                {
                    "id": 54,
                    "name": "bar",
                    "description": "Bar access"
                },
                {
                    "id": 4,
                    "name": "default",
                    "description": ""
                },
                {
                    "id": 53,
                    "name": "foo",
                    "description": "Foo access"
                },
                {
                    "id": 55,
                    "name": "plan_scope",
                    "description": "Plan scope access"
                }
            ]
        },
        {
            "id": 4,
            "name": "system",
            "scopes": [
                {
                    "id": 52,
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

        $this->assertEquals(404, $response->getStatusCode(), $body);
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

        $this->assertEquals(404, $response->getStatusCode(), $body);
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

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

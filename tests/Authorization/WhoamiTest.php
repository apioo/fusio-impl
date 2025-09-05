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

namespace Fusio\Impl\Tests\Authorization;

use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Normalizer;

/**
 * WhoamiTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class WhoamiTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/authorization/whoami', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "id": 2,
    "roleId": 3,
    "planId": 1,
    "status": 1,
    "name": "Consumer",
    "email": "consumer@localhost.com",
    "points": 100,
    "scopes": [
        "consumer",
        "consumer.account",
        "consumer.app",
        "consumer.event",
        "consumer.form",
        "consumer.grant",
        "consumer.identity",
        "consumer.log",
        "consumer.page",
        "consumer.payment",
        "consumer.plan",
        "consumer.scope",
        "consumer.token",
        "consumer.transaction",
        "consumer.webhook",
        "authorization",
        "openid",
        "foo",
        "bar",
        "plan_scope"
    ],
    "plans": [
        {
            "id": 2,
            "name": "Plan B",
            "price": 49.99,
            "points": 1000
        }
    ],
    "metadata": {
        "foo": "bar"
    },
    "date": "[datetime]"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

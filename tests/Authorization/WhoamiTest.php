<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * WhoamiTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class WhoamiTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/authorization/whoami', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "id": 1,
    "roleId": 1,
    "status": 1,
    "name": "Administrator",
    "email": "admin@localhost.com",
    "scopes": [
        "backend",
        "backend.account",
        "backend.action",
        "backend.app",
        "backend.audit",
        "backend.category",
        "backend.config",
        "backend.connection",
        "backend.cronjob",
        "backend.dashboard",
        "backend.event",
        "backend.generator",
        "backend.identity",
        "backend.log",
        "backend.marketplace",
        "backend.operation",
        "backend.page",
        "backend.plan",
        "backend.rate",
        "backend.role",
        "backend.schema",
        "backend.scope",
        "backend.sdk",
        "backend.statistic",
        "backend.tenant",
        "backend.token",
        "backend.transaction",
        "backend.trash",
        "backend.user",
        "backend.webhook",
        "consumer",
        "consumer.account",
        "consumer.app",
        "consumer.event",
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
        "default",
        "foo",
        "bar"
    ],
    "plans": [
        {
            "id": 2,
            "name": "Plan B",
            "price": 49.99,
            "points": 1000
        }
    ],
    "date": "[datetime]"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

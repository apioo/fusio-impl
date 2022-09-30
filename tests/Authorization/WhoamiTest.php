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

namespace Fusio\Impl\Tests\Authorization;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * WhoamiTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class WhoamiTest extends ControllerDbTestCase
{
    public function getDataSet()
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
        $body = preg_replace('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/m', '[datetime]', $body);

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
        "backend.log",
        "backend.marketplace",
        "backend.page",
        "backend.plan",
        "backend.rate",
        "backend.role",
        "backend.route",
        "backend.schema",
        "backend.scope",
        "backend.sdk",
        "backend.statistic",
        "backend.transaction",
        "backend.trash",
        "backend.user",
        "consumer",
        "consumer.app",
        "consumer.event",
        "consumer.grant",
        "consumer.log",
        "consumer.page",
        "consumer.payment",
        "consumer.plan",
        "consumer.scope",
        "consumer.subscription",
        "consumer.transaction",
        "consumer.user",
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
    "attributes": {
        "first_name": "Johann",
        "last_name": "Bach"
    },
    "date": "[datetime]"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

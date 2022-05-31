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

namespace Fusio\Impl\Tests\Backend\Api\Account;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * AccountTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class AccountTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/account', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/account.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/account', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "id": 4,
    "roleId": 2,
    "provider": 1,
    "status": 1,
    "name": "Developer",
    "email": "developer@localhost.com",
    "points": 10,
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
        "foo",
        "bar"
    ],
    "date": "2015-02-27T19:59:15Z"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/account', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/account', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a'
        ), json_encode([
            'email' => 'foo@bar.com',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "success": true,
    "message": "Account successful changed"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database password
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('email')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();
        $row = Environment::getService('connection')->fetchAssoc($sql, ['id' => 4]);

        $this->assertEquals('foo@bar.com', $row['email']);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/account', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

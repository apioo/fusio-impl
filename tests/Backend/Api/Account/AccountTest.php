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

namespace Fusio\Impl\Tests\Backend\Api\Account;

use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * AccountTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AccountTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/account', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer bae8116c20aaa2a13774345f4a5d98bacbb2062ae79122c9c4f5ea6b767c1b9a'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "id": 4,
    "roleId": 2,
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
        "backend.transaction",
        "backend.trash",
        "backend.user",
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
        "consumer.subscription",
        "consumer.transaction",
        "authorization",
        "foo",
        "bar"
    ],
    "date": "[datetime]"
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

        $this->assertEquals(404, $response->getStatusCode(), $body);
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
        $sql = $this->connection->createQueryBuilder()
            ->select('email')
            ->from('fusio_user')
            ->where('id = :id')
            ->getSQL();
        $row = $this->connection->fetchAssociative($sql, ['id' => 4]);

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

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

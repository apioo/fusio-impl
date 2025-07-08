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

namespace Fusio\Impl\Tests\Consumer\Api\App;

use Fusio\Impl\Table;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Normalizer;
use PSX\Framework\Test\Environment;
use PSX\Sql\TableManagerInterface;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/consumer/app/3', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $data = json_decode($body);
        $sec  = $data->appSecret ?? null;
        $body = str_replace(trim(json_encode($sec), '"'), '[app_secret]', $body);

        $expect = <<<'JSON'
{
    "id": 3,
    "userId": 2,
    "status": 1,
    "name": "Foo-App",
    "url": "http:\/\/google.com",
    "appKey": "[uuid]",
    "appSecret": "[app_secret]",
    "metadata": {
        "foo": "bar"
    },
    "scopes": [
        "authorization",
        "foo",
        "bar"
    ],
    "tokens": [
        {
            "id": 7,
            "status": 1,
            "name": "Foo-App\/Expired",
            "scope": [
                "bar"
            ],
            "ip": "127.0.0.1",
            "expire": "[datetime]",
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 1,
            "name": "Foo-App\/Developer",
            "scope": [
                "bar"
            ],
            "ip": "127.0.0.1",
            "expire": "[datetime]",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "status": 1,
            "name": "Foo-App\/Consumer",
            "scope": [
                "bar"
            ],
            "ip": "127.0.0.1",
            "expire": "[datetime]",
            "date": "[datetime]"
        }
    ],
    "date": "[datetime]"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/app/3', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/app/3', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'status' => 2,
            'name'   => 'Bar',
            'url'    => 'http://microsoft.com',
            'scopes' => ['foo', 'bar']
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "App successfully updated",
    "id": "3"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status', 'user_id', 'name', 'url')
            ->from('fusio_app')
            ->where('id = 2')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(2, $row['id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals(1, $row['user_id']);
        $this->assertEquals('Developer', $row['name']);
        $this->assertEquals('http://127.0.0.1/apps/developer', $row['url']);

        $scopes = Environment::getService(TableManagerInterface::class)->getTable(Table\App\Scope::class)->getAvailableScopes(null, 2);
        $scopes = Table\Scope::getNames($scopes);

        $this->assertEquals([
            'consumer',
            'consumer.account',
            'consumer.app',
            'consumer.event',
            'consumer.form',
            'consumer.grant',
            'consumer.identity',
            'consumer.log',
            'consumer.page',
            'consumer.payment',
            'consumer.plan',
            'consumer.scope',
            'consumer.token',
            'consumer.transaction',
            'consumer.webhook',
            'authorization',
            'default',
        ], $scopes);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/app/3', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "App successfully deleted",
    "id": "3"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_app')
            ->where('id = 3')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(3, $row['id']);
        $this->assertEquals(Table\App::STATUS_DELETED, $row['status']);
    }
}

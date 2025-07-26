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

namespace Fusio\Impl\Tests\Consumer\Api\Webhook;

use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Normalizer;

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
        $response = $this->sendRequest('/consumer/webhook/2', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "id": 2,
    "status": 1,
    "name": "pong",
    "event": "foo-event",
    "endpoint": "http:\/\/www.fusio-project.org\/ping",
    "responses": [
        {
            "status": 2,
            "attempts": 1,
            "code": 200,
            "executeDate": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/webhook/2', 'POST', array(
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
        $response = $this->sendRequest('/consumer/webhook/2', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'event' => 'foo-event',
            'name' => 'foofoo',
            'endpoint' => 'http://127.0.0.1/changed-callback.php',
        ]));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "success": true,
    "message": "Webhook successfully updated",
    "id": "2"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'event_id', 'user_id', 'status', 'name', 'endpoint')
            ->from('fusio_webhook')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => 2]);

        $this->assertEquals(2, $row['id']);
        $this->assertEquals(65, $row['event_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('foofoo', $row['name']);
        $this->assertEquals('http://127.0.0.1/changed-callback.php', $row['endpoint']);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/webhook/2', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "success": true,
    "message": "Webhook successfully deleted",
    "id": "2"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'event_id', 'user_id', 'status', 'endpoint')
            ->from('fusio_webhook')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => 2]);

        $this->assertEmpty($row);
    }
}

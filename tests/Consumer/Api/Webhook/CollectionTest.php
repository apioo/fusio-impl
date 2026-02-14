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

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/consumer/webhook', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 2,
            "status": 1,
            "name": "pong",
            "event": "foo-event",
            "endpoint": "http:\/\/www.fusio-project.org\/ping"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetSearch()
    {
        $response = $this->sendRequest('/consumer/webhook?search=on', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "totalResults": 1,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 2,
            "status": 1,
            "name": "pong",
            "event": "foo-event",
            "endpoint": "http:\/\/www.fusio-project.org\/ping"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/consumer/webhook', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'event' => 'foo-event',
            'name' => 'test',
            'endpoint' => 'http://127.0.0.1/new-callback.php',
        ]));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertInstanceOf(\stdClass::class, $data, $body);
        $this->assertTrue($data->success, $body);
        $this->assertEquals('Webhook successfully created', $data->success, $body);
        $this->assertNotEmpty($data->id, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'event_id', 'user_id', 'status', 'name', 'endpoint')
            ->from('fusio_webhook')
            ->where('id = :id')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['id' => $data->id]);

        $this->assertEquals($data->id, $row['id']);
        $this->assertEquals(72, $row['event_id']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals(1, $row['status']);
        $this->assertEquals('test', $row['name']);
        $this->assertEquals('http://127.0.0.1/new-callback.php', $row['endpoint']);
    }

    public function testPostEmptyEndpoint()
    {
        $response = $this->sendRequest('/consumer/webhook', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'event' => 'foo-event',
            'endpoint' => '',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertStringContainsString('Webhook endpoint must contain a value', $body, $body);
    }

    public function testPostInvalidEndpoint()
    {
        $response = $this->sendRequest('/consumer/webhook', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'event' => 'foo-event',
            'endpoint' => 'foobar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertStringContainsString('Webhook endpoint must be a valid url', $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/consumer/webhook', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/consumer/webhook', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b8f6f61bd22b440a3e4be2b7491066682bfcde611dbefa1b15d2e7f6522d77e2'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

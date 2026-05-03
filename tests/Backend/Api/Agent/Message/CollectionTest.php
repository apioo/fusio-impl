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

namespace Fusio\Impl\Tests\Backend\Api\Agent\Message;

use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Normalizer;
use PSX\Json\Parser;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends DbTestCase
{
    public function testGet(): void
    {
        $response = $this->sendRequest('/backend/agent/7/message', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body = (string) $response->getBody();
        $body = Normalizer::normalizeDateTime($body);

        $expect = <<<'JSON'
{
    "totalResults": 16,
    "startIndex": 0,
    "itemsPerPage": 10,
    "entry": [
        {
            "id": 1,
            "chatId": "41fd19b2-2dc0-46d9-b904-85c0d0b61a77",
            "role": "user",
            "item": {
                "type": "text",
                "content": "This is a test message"
            },
            "insertDate": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
    
    public function testGetParent(): void
    {
        $response = $this->sendRequest('/backend/agent/7/message?chat_id=41fd19b2-2dc0-46d9-b904-85c0d0b61a77', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body = (string) $response->getBody();
        $body = Normalizer::normalizeDateTime($body);

        $expect = <<<'JSON'
{
    "totalResults": 2,
    "startIndex": 0,
    "itemsPerPage": 10,
    "entry": [
        {
            "id": 1,
            "chatId": "41fd19b2-2dc0-46d9-b904-85c0d0b61a77",
            "role": "user",
            "item": {
                "type": "text",
                "content": "This is a test message"
            },
            "insertDate": "[datetime]"
        },
        {
            "id": 2,
            "chatId": "41fd19b2-2dc0-46d9-b904-85c0d0b61a77",
            "role": "assistant",
            "item": {
                "type": "text",
                "content": "And an agent response"
            },
            "insertDate": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost(): void
    {
        $response = $this->sendRequest('/backend/agent/7/message', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'item' => [
                'type' => 'text',
                'content' => 'What is the meaning of life?',
            ],
        ]));

        $body = (string) $response->getBody();
        $data = Parser::decode($body);

        $chatId = $data->id ?? null;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertNotEmpty($chatId);
        $this->assertEquals('text', $data->item->type);
        $this->assertEquals('The answer ist: 42', $data->item->content);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('chat_id', 'child', 'origin', 'content')
            ->from('fusio_agent_message')
            ->where('agent_id = :agent_id')
            ->orderBy('id', 'DESC')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['agent_id' => 7]);

        $this->assertEquals($chatId, $row['chat_id']);
        $this->assertEquals(1, $row['child']);
        $this->assertEquals(2, $row['origin']);
        $this->assertJsonStringEqualsJsonString(Parser::encode(['type' => 'text', 'content' => 'The answer ist: 42']), $row['content']);
    }

    public function testPut(): void
    {
        $response = $this->sendRequest('/backend/agent/6/message', 'PUT', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete(): void
    {
        $response = $this->sendRequest('/backend/agent/6/message', 'DELETE', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

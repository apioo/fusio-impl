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

namespace Fusio\Impl\Tests\Backend\Api\Test;

use Fusio\Impl\Table\Generated\TestTable;
use Fusio\Impl\Table\Test;
use Fusio\Impl\Tests\DbTestCase;

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
        $response = $this->sendRequest('/backend/test/1', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<JSON
{
    "id": 1,
    "status": 1,
    "operationName": "test.listFoo",
    "message": "message",
    "response": "response",
    "config": {
        "uriFragments": "foo=bar",
        "parameters": "foo=bar",
        "headers": "foo=bar",
        "body": {
            "foo": "bar"
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        $response = $this->sendRequest('/backend/test/10', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Could not find test', $data->message);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/test/1', 'POST', array(
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
        $payload = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/test/1', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status' => Test::STATUS_DISABLED,
            'config' => [
                'uriFragments' => 'bar=bar',
                'parameters' => 'bar=bar',
                'headers' => 'Content-Type=text/xml',
                'body' => $payload,
            ],
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Test successfully updated",
    "id": "1"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'status', 'uri_fragments', 'parameters', 'headers', 'body')
            ->from('fusio_test')
            ->where('id = 1')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql);

        $this->assertEquals(1, $row['id']);
        $this->assertEquals(Test::STATUS_DISABLED, $row['status']);
        $this->assertEquals('bar=bar', $row['uri_fragments']);
        $this->assertEquals('bar=bar', $row['parameters']);
        $this->assertEquals('Content-Type=text/xml', $row['headers']);
        $this->assertJsonStringEqualsJsonString(json_encode($payload), $row['body']);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/test/1', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    protected function isTransactional(): bool
    {
        return false;
    }
}

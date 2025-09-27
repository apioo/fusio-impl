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

namespace Fusio\Impl\Tests\Controller;

use Fusio\Impl\Base;
use Fusio\Impl\Tests\DbTestCase;

/**
 * JsonRPCTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class JsonRPCTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/jsonrpc', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testPost()
    {
        $body = <<<'JSON'
{
  "jsonrpc": "2.0",
  "method": "inspect.get",
  "params": {
    "foo": "bar",
    "payload": {
      "foo": "bar"
    }
  },
  "id": 1
}
JSON;

        $response = $this->sendRequest('/jsonrpc', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ], $body);

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
  "jsonrpc": "2.0",
  "result": {
    "arguments": {
      "foo": "bar"
    },
    "payload": {
      "foo": "bar"
    },
    "context": {
      "method": "inspect.get"
    }
  },
  "id": 1
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPostBatch()
    {
        $body = <<<'JSON'
[
  {
    "jsonrpc": "2.0",
    "method": "inspect.get",
    "params": {
      "foo": "bar",
      "payload": {
        "foo": "bar"
      }
    },
    "id": 1
  }, {
    "jsonrpc": "2.0",
    "method": "test.listFoo",
    "id": 2
  }
]
JSON;

        $response = $this->sendRequest('/jsonrpc', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ], $body);

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
[
  {
    "jsonrpc": "2.0",
    "result": {
      "arguments": {
        "foo": "bar"
      },
      "payload": {
        "foo": "bar"
      },
      "context": {
        "method": "inspect.get"
      }
    },
    "id": 1
  },
  {
    "jsonrpc": "2.0",
    "result": {
      "totalResults": 2,
      "itemsPerPage": 16,
      "startIndex": 0,
      "entry": [
        {
          "id": 2,
          "title": "bar",
          "content": "foo",
          "date": "2015-02-27T19:59:15+00:00"
        },
        {
          "id": 1,
          "title": "foo",
          "content": "bar",
          "date": "2015-02-27T19:59:15+00:00"
        }
      ]
    },
    "id": 2
  }
]
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/jsonrpc', 'PUT', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testPatch()
    {
        $response = $this->sendRequest('/jsonrpc', 'PATCH', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/jsonrpc', 'DELETE', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }
}

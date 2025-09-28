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
 * GraphQLTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GraphQLTest extends DbTestCase
{
    public function testGet()
    {
        $response = $this->sendRequest('/graphql?query={testListFoo{totalResults}}', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "data": {
        "testListFoo": {
            "totalResults": 2
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPost()
    {
        $body = <<<JSON
{
  "query": "{ testListFoo { totalResults } }"
}
JSON;

        $response = $this->sendRequest('/graphql', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ], $body);

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "data": {
        "testListFoo": {
            "totalResults": 2
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPostArguments()
    {
        $body = <<<JSON
{
  "query": "{ testListFoo(count: 1) { entry { title } } }"
}
JSON;

        $response = $this->sendRequest('/graphql', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ], $body);

        $actual = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "data": {
        "testListFoo": {
            "entry": {
                "title": "bar"
            }
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/graphql', 'PUT', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testPatch()
    {
        $response = $this->sendRequest('/graphql', 'PATCH', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/graphql', 'DELETE', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $this->assertEquals(404, $response->getStatusCode());
    }
}

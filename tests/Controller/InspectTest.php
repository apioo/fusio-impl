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

namespace Fusio\Impl\Tests\Controller;

use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
use PSX\Api\OperationInterface;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Json\Parser;

/**
 * InspectTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class InspectTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/inspect/bar?foo=bar', 'GET', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "arguments": {
        "foo": "bar"
    },
    "payload": {},
    "context": {
        "method": "GET",
        "headers": {
            "user-agent": [
                "Fusio TestCase"
            ],
            "authorization": [
                "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
            ]
        },
        "uri_fragments": {
            "foo": "bar"
        },
        "parameters": {
            "foo": "bar"
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGetError()
    {
        $response = $this->sendRequest('/inspect/bar?throw=1', 'GET', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $body = (string) $response->getBody();

        $this->assertEquals(500, $response->getStatusCode(), $body);

        $data = json_decode($body);

        $this->assertFalse($data->success);
        $this->assertEquals('Foobar', substr($data->message, 0, 6));
    }

    public function testPost()
    {
        $response = $this->sendRequest('/inspect/bar?foo=bar', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ], \json_encode(['foo' => 'bar']));

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "arguments": {
        "foo": "bar"
    },
    "payload": {
        "foo": "bar"
    },
    "context": {
        "method": "POST",
        "headers": {
            "user-agent": [
                "Fusio TestCase"
            ],
            "authorization": [
                "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
            ]
        },
        "uri_fragments": {
            "foo": "bar"
        },
        "parameters": {
            "foo": "bar"
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/inspect/bar?foo=bar', 'PUT', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ], \json_encode(['foo' => 'bar']));

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "arguments": {
        "foo": "bar"
    },
    "payload": {
        "foo": "bar"
    },
    "context": {
        "method": "PUT",
        "headers": {
            "user-agent": [
                "Fusio TestCase"
            ],
            "authorization": [
                "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
            ]
        },
        "uri_fragments": {
            "foo": "bar"
        },
        "parameters": {
            "foo": "bar"
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testPatch()
    {
        $response = $this->sendRequest('/inspect/bar?foo=bar', 'PATCH', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ], \json_encode(['foo' => 'bar']));

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "arguments": {
        "foo": "bar"
    },
    "payload": {
        "foo": "bar"
    },
    "context": {
        "method": "PATCH",
        "headers": {
            "user-agent": [
                "Fusio TestCase"
            ],
            "authorization": [
                "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
            ]
        },
        "uri_fragments": {
            "foo": "bar"
        },
        "parameters": {
            "foo": "bar"
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/inspect/bar?foo=bar', 'DELETE', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "arguments": {
        "foo": "bar"
    },
    "payload": {},
    "context": {
        "method": "DELETE",
        "headers": {
            "user-agent": [
                "Fusio TestCase"
            ],
            "authorization": [
                "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
            ]
        },
        "uri_fragments": {
            "foo": "bar"
        },
        "parameters": {
            "foo": "bar"
        }
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}

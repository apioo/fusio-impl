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
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class InspectTest extends ControllerDbTestCase
{
    private ?int $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = Fixture::getId('fusio_operation', 'test.listFoo');
    }

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

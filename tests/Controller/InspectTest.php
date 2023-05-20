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
use PSX\Api\Resource;
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
    "method": "GET",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ],
        "x-request-id": [
            "[uuid]"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": {
        "foo": "bar"
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

    public function testGetChangeStatus()
    {
        $stability = [
            OperationInterface::STABILITY_DEPRECATED,
            OperationInterface::STABILITY_EXPERIMENTAL,
            OperationInterface::STABILITY_STABLE,
            OperationInterface::STABILITY_LEGACY,
        ];

        foreach ($stability as $key => $status) {
            // update the route status
            $response = $this->sendRequest('/backend/routes/' . $this->id, 'PUT', [
                'User-Agent' => 'Fusio TestCase',
                'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
            ], json_encode([
                'path' => '/foo',
                'config' => [[
                    'version' => 1,
                    'status' => $status,
                    'methods' => [
                        'GET' => [
                            'active' => true,
                            'public' => true,
                            'action' => 'Inspect-Action',
                            'response' => 'Passthru',
                        ],
                    ],
                ]],
            ]));

            $actual = (string) $response->getBody();
            $expect = <<<'JSON'
{
    "success": true,
    "message": "Route successfully updated"
}
JSON;

            $this->assertEquals(200, $response->getStatusCode(), $actual);
            $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);

            // send request
            $response = $this->sendRequest('/inspect/bar', 'GET', [
                'User-Agent' => 'Fusio TestCase',
                'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
            ]);

            $actual = (string)$response->getBody();
            $actual = Normalizer::normalize($actual);

            if ($status === Resource::STATUS_CLOSED) {
                $data = Parser::decode($actual);

                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'ratelimit-limit' => ['3600'],
                    'ratelimit-remaining' => [3600 - $key],
                ];

                $this->assertEquals(410, $response->getStatusCode(), $actual);
                $this->assertEquals($headers, $response->getHeaders(), $actual);
                $this->assertEquals(false, $data->success, $actual);
                $this->assertEquals($debug ? 'PSX\\Http\\Exception\\GoneException' : 'Internal Server Error', $data->title, $actual);
                $this->assertEquals('Resource is not longer supported', substr($data->message, 0, 32), $actual);
            } else {
                $expect = <<<'JSON'
{
    "method": "GET",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ],
        "x-request-id": [
            "[uuid]"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": []
}
JSON;

                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'ratelimit-limit' => ['3600'],
                    'ratelimit-remaining' => [3600 - $key],
                ];

                if ($status === Resource::STATUS_DEVELOPMENT) {
                    $headers['warning'] = ['199 PSX "Resource is in development"'];
                } elseif ($status === Resource::STATUS_DEPRECATED) {
                    $headers['warning'] = ['199 PSX "Resource is deprecated"'];
                }

                $this->assertEquals(200, $response->getStatusCode(), $actual);
                $this->assertEquals($headers, $response->getHeaders(), $actual);
                $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
            }
        }
    }

    public function testGetChangeStatusError()
    {
        $stability = [
            OperationInterface::STABILITY_DEPRECATED,
            OperationInterface::STABILITY_EXPERIMENTAL,
            OperationInterface::STABILITY_STABLE,
            OperationInterface::STABILITY_LEGACY,
        ];

        foreach ($stability as $key => $status) {
            // update the route status
            $response = $this->sendRequest('/backend/operation/' . $this->id, 'PUT', [
                'User-Agent' => 'Fusio TestCase',
                'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
            ], json_encode([
                'status' => $status,
                'http_path' => '/foo',
                'action' => 'Inspect-Action',
            ]));

            $body = (string) $response->getBody();
            $expect = <<<'JSON'
{
    "success": true,
    "message": "Operation successfully updated"
}
JSON;

            $this->assertEquals(200, $response->getStatusCode(), $body);
            $this->assertJsonStringEqualsJsonString($expect, $body, $body);

            // send request
            $response = $this->sendRequest('/inspect/bar?throw=1', 'GET', [
                'User-Agent' => 'Fusio TestCase',
                'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
            ]);

            $body = (string) $response->getBody();

            if ($status === Resource::STATUS_CLOSED) {
                $data = Parser::decode($body);

                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'ratelimit-limit' => ['3600'],
                    'ratelimit-remaining' => [3600 - $key],
                ];

                $this->assertEquals(410, $response->getStatusCode(), $body);
                $this->assertEquals($headers, $response->getHeaders(), $body);
                $this->assertEquals(false, $data->success, $body);
                $this->assertEquals($debug ? 'PSX\\Http\\Exception\\GoneException' : 'Internal Server Error', $data->title, $body);
                $this->assertEquals('Resource is not longer supported', substr($data->message, 0, 32), $body);
            } else {
                $headers = [
                    'vary' => ['Accept'],
                    'content-type' => ['application/json'],
                    'ratelimit-limit' => ['3600'],
                    'ratelimit-remaining' => [3600 - $key],
                ];

                if ($status === Resource::STATUS_DEVELOPMENT) {
                    $headers['warning'] = ['199 PSX "Resource is in development"'];
                } elseif ($status === Resource::STATUS_DEPRECATED) {
                    $headers['warning'] = ['199 PSX "Resource is deprecated"'];
                }

                $this->assertEquals(500, $response->getStatusCode(), $body);
                $this->assertEquals($headers, $response->getHeaders(), $body);

                $data = json_decode($body);

                $this->assertFalse($data->success);
                $this->assertEquals('Foobar', substr($data->message, 0, 6));
            }
        }
    }

    public function testPost()
    {
        $response = $this->sendRequest('/inspect/bar?foo=bar', 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873'
        ]);

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "method": "POST",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ],
        "x-request-id": [
            "[uuid]"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": {
        "foo": "bar"
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
        ]);

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "method": "PUT",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ],
        "x-request-id": [
            "[uuid]"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": {
        "foo": "bar"
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
        ]);

        $actual = (string) $response->getBody();
        $actual = Normalizer::normalize($actual);

        $expect = <<<'JSON'
{
    "method": "PATCH",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ],
        "x-request-id": [
            "[uuid]"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": {
        "foo": "bar"
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
    "method": "DELETE",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "authorization": [
            "Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873"
        ],
        "x-request-id": [
            "[uuid]"
        ]
    },
    "uri_fragments": {
        "foo": "bar"
    },
    "parameters": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $actual);
        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }
}

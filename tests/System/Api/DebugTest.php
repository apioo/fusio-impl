<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\System\Api;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * DebugTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DebugTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/system/debug', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/debug.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/system/debug?foo=bar', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();
        $body = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/m', '00000000-0000-0000-0000-000000000000', $body);

        $expect = <<<'JSON'
{
    "body": {},
    "class": "Fusio\\Engine\\Request\\HttpRequest",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "x-request-id": [
            "00000000-0000-0000-0000-000000000000"
        ]
    },
    "method": "GET",
    "parameters": {
        "foo": "bar"
    },
    "uriFragments": []
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/system/debug', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();
        $body = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/m', '00000000-0000-0000-0000-000000000000', $body);

        $expect = <<<'JSON'
{
    "body": {
        "foo": "bar"
    },
    "class": "Fusio\\Engine\\Request\\HttpRequest",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "x-request-id": [
            "00000000-0000-0000-0000-000000000000"
        ]
    },
    "method": "POST",
    "parameters": [],
    "uriFragments": []
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/system/debug', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();
        $body = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/m', '00000000-0000-0000-0000-000000000000', $body);

        $expect = <<<'JSON'
{
    "body": {
        "foo": "bar"
    },
    "class": "Fusio\\Engine\\Request\\HttpRequest",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "x-request-id": [
            "00000000-0000-0000-0000-000000000000"
        ]
    },
    "method": "PUT",
    "parameters": [],
    "uriFragments": []
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/system/debug', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();
        $body = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/m', '00000000-0000-0000-0000-000000000000', $body);

        $expect = <<<'JSON'
{
    "body": {},
    "class": "Fusio\\Engine\\Request\\HttpRequest",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "x-request-id": [
            "00000000-0000-0000-0000-000000000000"
        ]
    },
    "method": "DELETE",
    "parameters": [],
    "uriFragments": []
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPatch()
    {
        $response = $this->sendRequest('/system/debug', 'PATCH', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();
        $body = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/m', '00000000-0000-0000-0000-000000000000', $body);

        $expect = <<<'JSON'
{
    "body": {
        "foo": "bar"
    },
    "class": "Fusio\\Engine\\Request\\HttpRequest",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ],
        "x-request-id": [
            "00000000-0000-0000-0000-000000000000"
        ]
    },
    "method": "PATCH",
    "parameters": [],
    "uriFragments": []
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

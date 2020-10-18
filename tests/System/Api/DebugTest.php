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
        $response = $this->sendRequest('/doc/*/export/debug', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/debug.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/export/debug?foo=bar', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "method": "GET",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ]
    },
    "parameters": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/export/debug', 'POST', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "method": "POST",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ]
    },
    "body": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/export/debug', 'PUT', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "method": "PUT",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ]
    },
    "body": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/export/debug', 'DELETE', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "method": "DELETE",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ]
    },
    "body": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPatch()
    {
        $response = $this->sendRequest('/export/debug', 'PATCH', array(
            'User-Agent' => 'Fusio TestCase',
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "method": "PATCH",
    "headers": {
        "user-agent": [
            "Fusio TestCase"
        ]
    },
    "body": {
        "foo": "bar"
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

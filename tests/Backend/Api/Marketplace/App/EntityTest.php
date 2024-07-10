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

namespace Fusio\Impl\Tests\Backend\Api\Marketplace\App;

use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        if (!Environment::getConfig('fusio_marketplace')) {
            $this->markTestSkipped('Marketplace not enabled');
        }

        $response = $this->sendRequest('/backend/marketplace/app/fusio', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body, true);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertNotEmpty($data['version']);
        $this->assertSame(version_compare($data['version'], '0.0'), 1);
        $this->assertNotEmpty($data['description']);
        $this->assertNotEmpty($data['screenshot']);
        $this->assertNotEmpty($data['website']);
        $this->assertNotEmpty($data['downloadUrl']);
        $this->assertNotEmpty($data['sha1Hash']);
        $this->assertNotEmpty($data['remote']);
    }

    public function testGetNotFound()
    {
        if (!Environment::getConfig('fusio_marketplace')) {
            $this->markTestSkipped('Marketplace not enabled');
        }

        $response = $this->sendRequest('/backend/marketplace/app/foobar', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Could not find local app', $data->message);
    }

    public function testPost()
    {
        if (!Environment::getConfig('fusio_marketplace')) {
            $this->markTestSkipped('Marketplace not enabled');
        }

        $response = $this->sendRequest('/backend/marketplace/app/fusio', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testPut()
    {
        if (!Environment::getConfig('fusio_marketplace')) {
            $this->markTestSkipped('Marketplace not enabled');
        }

        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/backend/marketplace/app/fusio', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "App is already up-to-date"
}
JSON;

        $this->assertEquals(400, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testDelete()
    {
        if (!Environment::getConfig('fusio_marketplace')) {
            $this->markTestSkipped('Marketplace not enabled');
        }

        $response = $this->sendRequest('/backend/marketplace/app/fusio', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "App fusio successful removed"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }
}

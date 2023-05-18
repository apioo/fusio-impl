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

namespace Fusio\Impl\Tests\Backend\Api\Marketplace;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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

        $response = $this->sendRequest('/backend/marketplace/fusio', 'GET', array(
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
        $response = $this->sendRequest('/backend/marketplace/foobar', 'GET', array(
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

        $response = $this->sendRequest('/backend/marketplace/fusio', 'POST', array(
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

        $response = $this->sendRequest('/backend/marketplace/fusio', 'PUT', array(
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

        $response = $this->sendRequest('/backend/marketplace/fusio', 'DELETE', array(
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

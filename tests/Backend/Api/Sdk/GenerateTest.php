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

namespace Fusio\Impl\Tests\Backend\Sdk;

use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * GenerateTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class GenerateTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $sdkDir = Environment::getConfig()->get('psx_path_public') . '/sdk';
        if (is_dir($sdkDir) && count(scandir($sdkDir)) > 2) {
            $this->markTestSkipped('The SDK folder already contains a release');
        }

        $response = $this->sendRequest('/backend/sdk', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "types": {
        "client-go": null,
        "client-java": null,
        "client-php": null,
        "client-typescript": null,
        "markup-client": null,
        "markup-html": null,
        "markup-markdown": null,
        "spec-openapi": null,
        "spec-raml": null,
        "spec-typeschema": null
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $sdkDir = Environment::getConfig()->get('psx_path_public') . '/sdk';
        if (is_dir($sdkDir) && count(scandir($sdkDir)) > 2) {
            $this->markTestSkipped('The SDK folder already contains a release');
        }

        $response = $this->sendRequest('/backend/sdk', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'format' => 'client-php',
        ]));

        $body = (string) $response->getBody();

        $expect = <<<'JSON'
{
    "success": true,
    "message": "SDK successfully generated",
    "link": "http:\/\/127.0.0.1\/sdk\/sdk-client-php-external.zip"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check zip file
        $this->assertTrue(is_file(Environment::getConfig()->get('psx_path_public') . '/sdk/sdk-client-php-external.zip'));
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/sdk', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/sdk', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }
}

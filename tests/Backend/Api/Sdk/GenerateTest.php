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

namespace Fusio\Impl\Tests\Backend\Sdk;

use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;

/**
 * GenerateTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GenerateTest extends DbTestCase
{
    public function testGet()
    {
        $sdkDir = Environment::getConfig('psx_path_public') . '/sdk';
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
        "client-php": null,
        "client-typescript": null,
        "server-php": null,
        "server-typescript": null,
        "markup-client": null,
        "markup-html": null,
        "markup-markdown": null,
        "spec-typeapi": null,
        "spec-openapi": null,
        "model-csharp": null,
        "model-go": null,
        "model-graphql": null,
        "model-html": null,
        "model-java": null,
        "model-jsonschema": null,
        "model-kotlin": null,
        "model-markdown": null,
        "model-php": null,
        "model-protobuf": null,
        "model-python": null,
        "model-ruby": null,
        "model-rust": null,
        "model-swift": null,
        "model-typescript": null,
        "model-typeschema": null,
        "model-visualbasic": null
    }
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $sdkDir = Environment::getConfig('psx_path_public') . '/sdk';
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
    "link": "http:\/\/127.0.0.1\/sdk\/sdk-client-php-app.zip"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check zip file
        $this->assertTrue(is_file(Environment::getConfig('psx_path_public') . '/sdk/sdk-client-php-app.zip'));
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

        $this->assertEquals(404, $response->getStatusCode(), $body);
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

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

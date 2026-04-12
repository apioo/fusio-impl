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

namespace Fusio\Impl\Tests\Backend\Api\Test;

use Fusio\Impl\Tests\DbTestCase;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends DbTestCase
{
    public function testGet()
    {
        $this->sendRequest('/backend/test', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
        ]));

        $this->sendRequest('/backend/test', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
        ]));

        $response = $this->sendRequest('/backend/test', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_TestCollection",
    "totalResults": 10,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_Test",
            "id": 10,
            "status": 4,
            "operationName": "mime.json",
            "message": "Expected status code 200 got 500"
        },
        {
            "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_Test",
            "id": 9,
            "status": 4,
            "operationName": "mime.form",
            "message": "Expected status code 200 got 500"
        },
        {
            "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_Test",
            "id": 2,
            "status": 4,
            "operationName": "inspect.delete",
            "message": "Missing parameter \"foo\" in path"
        },
        {
            "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_Test",
            "id": 4,
            "status": 4,
            "operationName": "inspect.patch",
            "message": "Missing parameter \"foo\" in path"
        },
        {
            "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_Test",
            "id": 6,
            "status": 4,
            "operationName": "inspect.put",
            "message": "Missing parameter \"foo\" in path"
        },
        {
            "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_Test",
            "id": 5,
            "status": 4,
            "operationName": "inspect.post",
            "message": "Missing parameter \"foo\" in path"
        },
        {
            "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_Test",
            "id": 3,
            "status": 4,
            "operationName": "inspect.get",
            "message": "Missing parameter \"foo\" in path"
        },
        {
            "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_Test",
            "id": 14,
            "status": 4,
            "operationName": "test.createBar",
            "message": "Expected status code 201 got 402"
        },
        {
            "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_Test",
            "id": 15,
            "status": 4,
            "operationName": "test.createFoo",
            "message": "Expected status code 201 got 402"
        },
        {
            "@type": "https://typehub.cloud/s/fusio/sdk/7.0.7/Backend_Test",
            "id": 7,
            "message": "/ property \"@type\" is unknown",
            "operationName": "meta.getAbout",
            "status": 4
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/test', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Tests successfully executed"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/test', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Tests successfully refreshed"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/test', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    protected function isTransactional(): bool
    {
        return false;
    }
}

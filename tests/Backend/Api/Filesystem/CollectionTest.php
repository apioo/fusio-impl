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

namespace Fusio\Impl\Tests\Backend\Api\Filesystem;

use Doctrine\DBAL\Types\Type;
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
        $response = $this->sendRequest('/backend/filesystem/LocalFilesystem', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "totalResults": 3,
    "itemsPerPage": 16,
    "startIndex": 0,
    "entry": [
        {
            "id": "385ee9e8-53fe-3082-8719-352b32044b13",
            "fileName": "collection_schema.json",
            "contentType": "application\/json",
            "checksum": "3451fb8575308d844ea3ed9f1ac60e2e",
            "lastModified": 1605125517
        },
        {
            "id": "7564504c-bfbb-387b-9ab2-bd937fa1dab7",
            "fileName": "entry_form.json",
            "contentType": "application\/json",
            "checksum": "669c26419e3be9dc363b017432661949",
            "lastModified": 1530430810
        },
        {
            "id": "79accfa8-013d-3d8d-8b5c-d7eba46910bf",
            "fileName": "entry_schema.json",
            "contentType": "application\/json",
            "checksum": "652eef86cffe31cf4de1dd37d883398d",
            "lastModified": 1605125517
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $this->markTestSkipped('File upload is difficult to test');
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/filesystem/LocalFilesystem', 'PUT', array(
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
        $response = $this->sendRequest('/backend/filesystem/LocalFilesystem', 'DELETE', array(
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

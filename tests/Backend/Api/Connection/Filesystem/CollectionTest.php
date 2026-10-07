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

namespace Fusio\Impl\Tests\Backend\Api\Connection\Filesystem;

use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Normalizer;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends DbTestCase
{
    public function testGet(): void
    {
        $response = $this->sendRequest('/backend/connection/LocalFilesystem/filesystem', 'GET', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body = (string) $response->getBody();
        $body = Normalizer::normalizeDateTime($body);
        $body = preg_replace('/[0-9a-f]{32}/m', '[checksum]', $body);

        $expect = <<<'JSON'
{
    "totalResults": 5,
    "itemsPerPage": 16,
    "startIndex": 0,
    "entry": [
        {
            "id": "bd7dfa50-677d-3a48-92fa-0cee40732ff3",
            "name": "collection_schema.json",
            "contentType": "application\/json",
            "checksum": "[checksum]",
            "lastModified": "[datetime]",
            "size": 812
        },
        {
            "id": "38496a0b-a4d7-3f66-adc4-eb3be4578ab0",
            "name": "entry_form.json",
            "contentType": "application\/json",
            "checksum": "[checksum]",
            "lastModified": "[datetime]",
            "size": 167
        },
        {
            "id": "c8a46340-4f76-3abe-be33-853376ea26af",
            "name": "entry_schema.json",
            "contentType": "application\/json",
            "checksum": "[checksum]",
            "lastModified": "[datetime]",
            "size": 379
        },
        {
            "id": "245e9edc-eecc-385d-9cf4-ee173f13817d",
            "name": "local-php-fix.php",
            "contentType": "text\/x-php",
            "checksum": "[checksum]",
            "lastModified": "[datetime]",
            "size": 408
        },
        {
            "id": "09e96122-91e0-3921-aaa3-547040e15666",
            "name": "local-php.php",
            "contentType": "text\/x-php",
            "checksum": "[checksum]",
            "lastModified": "[datetime]",
            "size": 408
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost(): void
    {
        $this->markTestSkipped('File upload is difficult to test');
    }

    public function testPut(): void
    {
        $response = $this->sendRequest('/backend/connection/LocalFilesystem/filesystem', 'PUT', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    public function testDelete(): void
    {
        $response = $this->sendRequest('/backend/connection/LocalFilesystem/filesystem', 'DELETE', [
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }

    #[\Override]
    protected function isTransactional(): bool
    {
        return false;
    }
}

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

namespace Fusio\Impl\Tests\Backend\Api\Page;

use Fusio\Impl\Backend;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * CollectionTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class CollectionTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/page', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "totalResults": 6,
    "startIndex": 0,
    "itemsPerPage": 16,
    "entry": [
        {
            "id": 3,
            "status": 1,
            "title": "API",
            "slug": "api",
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 1,
            "title": "Authorization",
            "slug": "authorization",
            "date": "[datetime]"
        },
        {
            "id": 2,
            "status": 1,
            "title": "Getting started",
            "slug": "getting-started",
            "date": "[datetime]"
        },
        {
            "id": 1,
            "status": 2,
            "title": "Overview",
            "slug": "overview",
            "date": "[datetime]"
        },
        {
            "id": 6,
            "status": 1,
            "title": "SDK",
            "slug": "sdk",
            "date": "[datetime]"
        },
        {
            "id": 5,
            "status": 1,
            "title": "Support",
            "slug": "support",
            "date": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetCount()
    {
        $response = $this->sendRequest('/backend/page?count=80', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<'JSON'
{
    "totalResults": 6,
    "startIndex": 0,
    "itemsPerPage": 80,
    "entry": [
        {
            "id": 3,
            "status": 1,
            "title": "API",
            "slug": "api",
            "date": "[datetime]"
        },
        {
            "id": 4,
            "status": 1,
            "title": "Authorization",
            "slug": "authorization",
            "date": "[datetime]"
        },
        {
            "id": 2,
            "status": 1,
            "title": "Getting started",
            "slug": "getting-started",
            "date": "[datetime]"
        },
        {
            "id": 1,
            "status": 2,
            "title": "Overview",
            "slug": "overview",
            "date": "[datetime]"
        },
        {
            "id": 6,
            "status": 1,
            "title": "SDK",
            "slug": "sdk",
            "date": "[datetime]"
        },
        {
            "id": 5,
            "status": 1,
            "title": "Support",
            "slug": "support",
            "date": "[datetime]"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetUnauthorized()
    {
        $response = $this->sendRequest('/backend/page', 'GET', array(
            'User-Agent' => 'Fusio TestCase',
        ));

        $body = (string) $response->getBody();
        $data = \json_decode($body);

        $this->assertEquals(401, $response->getStatusCode(), $body);
        $this->assertFalse($data->success);
        $this->assertStringStartsWith('Missing authorization header', $data->message);
    }

    public function testPost()
    {
        $metadata = [
            'foo' => 'bar'
        ];

        $response = $this->sendRequest('/backend/page', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status'   => 1,
            'title'    => 'My new page',
            'content'  => '<p>And here some content</p>',
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Page successfully created"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'title', 'content', 'metadata')
            ->from('fusio_page')
            ->where('slug = :slug')
            ->getSQL();

        $row = $this->connection->fetchAssociative($sql, ['slug' => 'my-new-page']);

        $this->assertEquals('My new page', $row['title']);
        $this->assertEquals('<p>And here some content</p>', $row['content']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/page', 'PUT', array(
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
        $response = $this->sendRequest('/backend/page', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

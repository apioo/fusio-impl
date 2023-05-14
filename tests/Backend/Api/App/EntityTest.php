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

namespace Fusio\Impl\Tests\Backend\Api\App;

use Fusio\Impl\Table;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use Fusio\Impl\Tests\Normalizer;
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
    private int $id;

    protected function setUp(): void
    {
        parent::setUp();

        $this->id = Fixture::getId('fusio_app', 'Foo-App');
    }

    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/app/' . $this->id, 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body = (string) $response->getBody();
        $body = Normalizer::normalize($body);

        $expect = <<<JSON
{
    "id": 3,
    "userId": 2,
    "status": 1,
    "name": "Foo-App",
    "url": "http:\/\/google.com",
    "parameters": "",
    "appKey": "[uuid]",
    "appSecret": "342cefac55939b31cd0a26733f9a4f061c0829ed87dae7caff50feaa55aff23d",
    "scopes": [
        "authorization",
        "foo",
        "bar"
    ],
    "tokens": [
        {
            "id": 4,
            "userId": 4,
            "status": 1,
            "token": "e4a4d21e8ca88b215572b4d8635c492d8877fd8d3de6b98ba7c08d282adfb94f",
            "scope": [
                "bar"
            ],
            "ip": "127.0.0.1",
            "expire": "[datetime]",
            "date": "[datetime]"
        },
        {
            "id": 3,
            "userId": 2,
            "status": 1,
            "token": "b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873",
            "scope": [
                "bar"
            ],
            "ip": "127.0.0.1",
            "expire": "[datetime]",
            "date": "[datetime]"
        }
    ],
    "metadata": {
        "foo": "bar"
    },
    "date": "[datetime]"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/backend/app/10', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Could not find app"
}
JSON;

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/app/' . $this->id, 'POST', array(
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
        $metadata = ['foo' => 'bar'];

        $response = $this->sendRequest('/backend/app/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status'   => 2,
            'userId'   => 2,
            'name'     => 'Bar',
            'url'      => 'http://microsoft.com',
            'scopes'   => ['foo', 'bar'],
            'metadata' => $metadata,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "App successfully updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'user_id', 'name', 'url', 'parameters', 'metadata')
            ->from('fusio_app')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(2, $row['status']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals('Bar', $row['name']);
        $this->assertEquals('http://microsoft.com', $row['url']);
        $this->assertEquals('', $row['parameters']);
        $this->assertJsonStringEqualsJsonString(json_encode($metadata), $row['metadata']);

        /** @var Table\App\Scope $table */
        $table = Environment::getService('table_manager')->getTable(Table\App\Scope::class);
        $scopes = $table->getAvailableScopes($this->id);
        $scopes = Table\Scope::getNames($scopes);

        $this->assertEquals(['foo', 'bar'], $scopes);
    }

    public function testPutWithParameters()
    {
        $response = $this->sendRequest('/backend/app/' . $this->id, 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'status'     => 2,
            'userId'     => 2,
            'name'       => 'Bar',
            'url'        => 'http://microsoft.com',
            'parameters' => 'foo=bar',
            'scopes'     => ['foo', 'bar']
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "App successfully updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status', 'user_id', 'name', 'url', 'parameters')
            ->from('fusio_app')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(2, $row['status']);
        $this->assertEquals(2, $row['user_id']);
        $this->assertEquals('Bar', $row['name']);
        $this->assertEquals('http://microsoft.com', $row['url']);
        $this->assertEquals('foo=bar', $row['parameters']);

        /** @var Table\App\Scope $table */
        $table = Environment::getService('table_manager')->getTable(Table\App\Scope::class);
        $scopes = $table->getAvailableScopes($this->id);
        $scopes = Table\Scope::getNames($scopes);

        $this->assertEquals(['foo', 'bar'], $scopes);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/app/' . $this->id, 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "App successfully deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_app')
            ->where('id = ' . $this->id)
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(Table\App::STATUS_DELETED, $row['status']);
    }
}

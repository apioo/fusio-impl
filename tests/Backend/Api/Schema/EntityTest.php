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

namespace Fusio\Impl\Tests\Backend\Api\Schema;

use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * EntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class EntityTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testDocumentation()
    {
        $response = $this->sendRequest('/system/doc/*/backend/schema/1', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $actual = Documentation::getResource($response);
        $expect = file_get_contents(__DIR__ . '/resource/entity.json');

        $this->assertJsonStringEqualsJsonString($expect, $actual, $actual);
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/schema/1', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "id": 1,
    "status": 1,
    "name": "Passthru",
    "source": {
        "definitions": {
            "Passthru": {
                "description": "No schema information available",
                "type": "object"
            }
        },
        "$ref": "Passthru"
    },
    "readonly": true
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetNotFound()
    {
        Environment::getContainer()->get('config')->set('psx_debug', false);

        $response = $this->sendRequest('/backend/schema/200', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": false,
    "title": "Internal Server Error",
    "message": "Could not find schema"
}
JSON;

        $this->assertEquals(404, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/schema/2', 'POST', array(
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
        $schema = [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string'
                ],
                'foo' => [
                    'type' => 'string'
                ],
                'bar' => [
                    'type' => 'string'
                ],
            ],
        ];

        $response = $this->sendRequest('/backend/schema/2', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'   => 'Test-Schema',
            'source' => \json_encode($schema),
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Schema successful updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertSchema('Test-Schema', json_encode($schema));
    }

    public function testPutForm()
    {
        $schema = \json_encode([
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string'
                ],
                'foo' => [
                    'type' => 'string'
                ],
                'bar' => [
                    'type' => 'string'
                ],
            ],
        ]);

        $form = \json_encode([
            'title' => [
                'ui:autofocus' => true,
                'ui:emptyValue' => ''
            ]
        ]);

        $response = $this->sendRequest('/backend/schema/2', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'name'   => 'Form-Schema',
            'source' => $schema,
            'form'   => $form,
        ]));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Schema successful updated"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        Assert::assertSchema('Form-Schema', $schema, $form);
    }

    public function testDelete()
    {
        // remove all responses and methods so that we can delete the schema
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->delete('fusio_routes_response')
            ->getSQL();

        Environment::getService('connection')->executeUpdate($sql);

        $sql = Environment::getService('connection')->createQueryBuilder()
            ->delete('fusio_routes_method')
            ->where('parameters = :parameters OR request = :request')
            ->getSQL();

        Environment::getService('connection')->executeUpdate($sql, ['parameters' => 2, 'request' => 2]);

        $response = $this->sendRequest('/backend/schema/2', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Schema successful deleted"
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        // check database
        $sql = Environment::getService('connection')->createQueryBuilder()
            ->select('id', 'status')
            ->from('fusio_schema')
            ->where('id = 2')
            ->getSQL();

        $row = Environment::getService('connection')->fetchAssoc($sql);

        $this->assertEquals(2, $row['id']);
        $this->assertEquals(0, $row['status']);
    }
}

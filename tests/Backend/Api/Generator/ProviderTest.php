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

namespace Fusio\Impl\Tests\Backend\Api\Generator;

use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Controller\SqlEntityTest;
use Fusio\Impl\Tests\Fixture;
use PSX\Api\OperationInterface;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * ProviderTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ProviderTest extends ControllerDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SqlEntityTest::dropAppTables($this->connection);
    }

    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testGet()
    {
        $response = $this->sendRequest('/backend/generator/testprovider', 'GET', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ));

        $body   = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "element": [
        {
            "element": "input",
            "name": "table",
            "title": "Table",
            "help": null,
            "type": "text"
        }
    ]
}
JSON;

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost()
    {
        $response = $this->sendRequest('/backend/generator/testprovider', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path' => '/provider',
            'scopes' => ['provider'],
            'public' => true,
            'config' => [
                'table' => 'foobar'
            ],
        ]));

        $body = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Provider successfully executed"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $data = json_decode(file_get_contents(__DIR__ . '/resource/changelog_test.json'));

        // check schema
        foreach ($data->schemas as $schema) {
            Assert::assertSchema($this->connection, 'Provider_' . $schema->name, json_encode($schema->source));
        }

        // check action
        foreach ($data->actions as $action) {
            Assert::assertAction($this->connection, 'Provider_' . $action->name, $action->class, json_encode($action->config));
        }

        // check operations
        foreach ($data->operations as $operation) {
            $path = '/provider' . $operation->httpPath;
            Assert::assertOperation($this->connection, OperationInterface::STABILITY_EXPERIMENTAL, 'provider.' . $operation->name, $operation->httpMethod, $path, ['provider']);
        }
    }

    public function testPostSqlEntity()
    {
        $typeSchema = \json_decode(file_get_contents(__DIR__ . '/resource/typeschema.json'));

        $response = $this->sendRequest('/backend/generator/sqlentity', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path' => '/provider',
            'scopes' => ['provider'],
            'public' => true,
            'config' => [
                'connection' => 1,
                'schema' => $typeSchema,
            ],
        ]));

        $body = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Provider successfully executed"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $data = file_get_contents(__DIR__ . '/resource/changelog_sqlentity.json');
        $data = str_replace('schema:\/\/', 'schema:\/\/Provider_', $data);
        $data = json_decode($data);

        // check schema
        foreach ($data->schemas as $schema) {
            Assert::assertSchema($this->connection, 'Provider_' . $schema->name, json_encode($schema->source));
        }

        // check action
        foreach ($data->actions as $action) {
            Assert::assertAction($this->connection, 'Provider_' . $action->name, $action->class, json_encode($action->config));
        }

        // check routes
        foreach ($data->operations as $operation) {
            $path = rtrim('/provider' . $operation->httpPath, '/');
            Assert::assertOperation($this->connection, OperationInterface::STABILITY_EXPERIMENTAL, 'provider.' . $operation->name, $operation->httpMethod, $path, ['provider']);
        }
    }

    public function testPostFileDirectory()
    {
        $response = $this->sendRequest('/backend/generator/filedirectory', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path' => '/provider',
            'scopes' => ['provider'],
            'public' => true,
            'config' => [
                'directory' => __DIR__ . '/resource',
            ],
        ]));

        $body = (string) $response->getBody();
        $expect = <<<'JSON'
{
    "success": true,
    "message": "Provider successfully executed"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        $data = file_get_contents(__DIR__ . '/resource/changelog_filedirectory.json');
        $data = str_replace('schema:\/\/', 'schema:\/\/Provider_', $data);
        $data = json_decode($data);

        // check schema
        foreach ($data->schemas as $schema) {
            Assert::assertSchema($this->connection, 'Provider_' . $schema->name, json_encode($schema->source));
        }

        // check action
        foreach ($data->actions as $action) {
            Assert::assertAction($this->connection, 'Provider_' . $action->name, $action->class, json_encode($action->config));
        }

        // check routes
        foreach ($data->operations as $operation) {
            $path = rtrim('/provider' . $operation->httpPath, '/');
            Assert::assertOperation($this->connection, OperationInterface::STABILITY_EXPERIMENTAL, 'provider.' . $operation->name, $operation->httpMethod, $path, ['provider']);
        }
    }

    public function testPut()
    {
        $response = $this->sendRequest('/backend/generator/testprovider', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'table' => 'foobar'
        ]));

        $body   = (string) $response->getBody();
        $expect = file_get_contents(__DIR__ . '/resource/changelog_test.json');

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPutSqlEntity()
    {
        $typeSchema = \json_decode(file_get_contents(__DIR__ . '/resource/typeschema.json'));

        $response = $this->sendRequest('/backend/generator/sqlentity', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'connection' => 1,
            'schema' => $typeSchema,
        ]));

        $body   = (string) $response->getBody();
        $expect = file_get_contents(__DIR__ . '/resource/changelog_sqlentity.json');

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPutFileDirectory()
    {
        $response = $this->sendRequest('/backend/generator/filedirectory', 'PUT', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'directory' => __DIR__ . '/resource',
        ]));

        $body   = (string) $response->getBody();
        $expect = file_get_contents(__DIR__ . '/resource/changelog_filedirectory.json');

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/generator/testprovider', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(404, $response->getStatusCode(), $body);
    }
}

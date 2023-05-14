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

namespace Fusio\Impl\Tests\Backend\Api\Generator;

use Fusio\Impl\Service\Route\Config;
use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\Controller\SqlEntityTest;
use Fusio\Impl\Tests\Documentation;
use Fusio\Impl\Tests\Fixture;
use PSX\Api\Resource;
use PSX\Framework\Test\ControllerDbTestCase;

/**
 * ProviderTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
            Assert::assertSchema('Provider_' . $schema->name, json_encode($schema->source));
        }

        // check action
        foreach ($data->actions as $action) {
            Assert::assertAction('Provider_' . $action->name, $action->class, json_encode($action->config));
        }

        // check routes
        foreach ($data->routes as $route) {
            $path = '/provider' . $route->path;
            Assert::assertRoute($path, ['foo', 'bar', 'provider'], $this->convertConfig($route->config, $data, $path));
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
        $data = str_replace('schema:\/\/\/', 'schema:\/\/\/Provider_', $data);
        $data = json_decode($data);

        // check schema
        foreach ($data->schemas as $schema) {
            Assert::assertSchema('Provider_' . $schema->name, json_encode($schema->source));
        }

        // check action
        foreach ($data->actions as $action) {
            Assert::assertAction('Provider_' . $action->name, $action->class, json_encode($action->config));
        }

        // check routes
        foreach ($data->routes as $route) {
            $path = '/provider' . $route->path;
            Assert::assertRoute($path, ['provider'], $this->convertConfig($route->config, $data, $path));
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

    public function testDelete()
    {
        $response = $this->sendRequest('/backend/generator/testprovider', 'DELETE', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'foo' => 'bar',
        ]));

        $body = (string) $response->getBody();

        $this->assertEquals(405, $response->getStatusCode(), $body);
    }

    private function convertConfig(array $configs, \stdClass $data, string $path): array
    {
        $result = [];
        foreach ($configs as $config) {
            foreach ($config->methods as $methodName => $method) {
                $newConfig = [
                    'method'       => $methodName,
                    'version'      => 1,
                    'status'       => Resource::STATUS_DEVELOPMENT,
                    'active'       => 1,
                    'public'       => 1,
                    'description'  => $method->description ?? null,
                    'operation_id' => $method->operationId ?? Config::buildOperationId($path, $methodName),
                    'parameters'   => isset($method->parameters) ? 'Provider_' . $this->findSchemaByIndex($method->parameters, $data) : null,
                    'request'      => isset($method->request) ? 'Provider_' . $this->findSchemaByIndex($method->request, $data) : null,
                    'responses'    => [],
                    'action'       => 'Provider_' . $this->findActionByIndex($method->action, $data),
                    'costs'        => $method->costs ?? null,
                ];

                if (isset($method->responses)) {
                    foreach ($method->responses as $statusCode => $response) {
                        $newConfig['responses'][$statusCode] = 'Provider_' . $this->findSchemaByIndex($response, $data);
                    }
                }

                $result[] = $newConfig;
            }
        }
        return $result;
    }

    private function findSchemaByIndex(int $index, \stdClass $data): string
    {
        return $data->schemas[$index]->name ?? throw new \RuntimeException('Provided an invalid index');
    }

    private function findActionByIndex(int $index, \stdClass $data): string
    {
        return $data->actions[$index]->name ?? throw new \RuntimeException('Provided an invalid index');
    }
}

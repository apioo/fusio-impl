<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Tests\Backend\Api\Generator;

use Fusio\Engine\Inflection\ClassName;
use Fusio\Impl\Tests\Assert;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Api\OperationInterface;

/**
 * ProviderTestCase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
abstract class ProviderTestCase extends DbTestCase
{
    public function testGet(): void
    {
        $class = ClassName::serialize($this->getProviderClass());

        $response = $this->sendRequest('/backend/generator/' . $class, 'GET', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ]);

        $body = (string) $response->getBody();
        $expect = $this->getExpectForm();

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testPost(): void
    {
        $class = ClassName::serialize($this->getProviderClass());

        $response = $this->sendRequest('/backend/generator/' . $class, 'POST', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode([
            'path' => '/provider',
            'scopes' => ['provider'],
            'public' => true,
            'config' => $this->getProviderConfig(),
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

        $data = json_decode($this->getExpectSchema());

        // check schema
        foreach ($data->schemas as $schema) {
            Assert::assertSchema($this->connection, 'Provider_' . $schema->name, json_encode($schema->source));
        }

        // check action
        foreach ($data->actions as $action) {
            Assert::assertAction($this->connection, 'Provider_' . $action->name, ClassName::serialize($action->class), json_encode($action->config));
        }

        // check operations
        foreach ($data->operations as $operation) {
            $path = rtrim('/provider' . $operation->httpPath, '/');
            Assert::assertOperation($this->connection, OperationInterface::STABILITY_EXPERIMENTAL, 'provider.' . $operation->name, $operation->httpMethod, $path, null, ['provider']);
        }
    }

    public function testPut(): void
    {
        $class = ClassName::serialize($this->getProviderClass());

        $response = $this->sendRequest('/backend/generator/' . $class, 'PUT', [
            'User-Agent' => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ], json_encode($this->getProviderConfig()));

        $body = (string) $response->getBody();
        $expect = $this->getExpectChangelog();

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    abstract protected function getProviderClass(): string;
    abstract protected function getProviderConfig(): array;
    abstract protected function getExpectChangelog(): string;
    abstract protected function getExpectForm(): string;

    protected function getExpectSchema(): string
    {
        return $this->getExpectChangelog();
    }

    protected function isTransactional(): bool
    {
        return false;
    }
}

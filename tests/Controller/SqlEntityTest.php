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

namespace Fusio\Impl\Tests\Controller;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Tests\DbTestCase;

/**
 * SqlEntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class SqlEntityTest extends DbTestCase
{
    public function testExecuteProviderAndCallRoutes()
    {
        $this->executeProvider();

        $categoryId = $this->createCategory('foo');
        $locationId = $this->createLocation('foo', '50.883331', '8.016667');
        $this->createHuman('Christoph', 'Kappestein', $locationId, ['foo', 'bar'], [$categoryId], ['foo' => $categoryId, 'bar' => $categoryId]);

        $this->assertResponse('/provider/category', __DIR__ . '/resource/category_collection.json');
        $this->assertResponse('/provider/category/1', __DIR__ . '/resource/category_entity.json');
        $this->assertResponse('/provider/location', __DIR__ . '/resource/location_collection.json');
        $this->assertResponse('/provider/location/1', __DIR__ . '/resource/location_entity.json');
        $this->assertResponse('/provider/human', __DIR__ . '/resource/human_collection.json');
        $this->assertResponse('/provider/human/1', __DIR__ . '/resource/human_entity.json');
    }

    private function createCategory(string $name): int
    {
        $response = $this->sendRequest('/provider/category', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
        ], json_encode([
            'name' => $name,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $expect = <<<'JSON'
{
    "success": true,
    "message": "Entry successfully created",
    "id": "1"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        return $data->id;
    }

    private function createLocation(string $name, string $lat, string $long): int
    {
        $response = $this->sendRequest('/provider/location', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
        ], json_encode([
            'name' => $name,
            'latitude' => $lat,
            'longitude' => $long,
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $expect = <<<'JSON'
{
    "success": true,
    "message": "Entry successfully created",
    "id": "1"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        return $data->id;
    }

    private function createHuman(string $firstName, string $lastName, int $locationId, array $tags, array $categoryIds, array $mapIds): int
    {
        $response = $this->sendRequest('/provider/human', 'POST', [
            'User-Agent'    => 'Fusio TestCase',
        ], json_encode([
            'firstName'  => $firstName,
            'lastName' => $lastName,
            'location' => ['id' => $locationId],
            'tags' => $tags,
            'categories' => array_map(fn (int $categoryId) => ['id' => $categoryId], $categoryIds),
            'map' => array_map(fn (int $mapIds) => ['id' => $mapIds], $mapIds),
        ]));

        $body = (string) $response->getBody();
        $data = json_decode($body);

        $expect = <<<'JSON'
{
    "success": true,
    "message": "Entry successfully created",
    "id": "1"
}
JSON;

        $this->assertEquals(201, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);

        return $data->id;
    }

    private function executeProvider(): void
    {
        $typeSchema = \json_decode(file_get_contents(__DIR__ . '/../Backend/Api/Generator/resource/typeschema.json'));

        $response = $this->sendRequest('/backend/generator/sqlentity', 'POST', array(
            'User-Agent'    => 'Fusio TestCase',
            'Authorization' => 'Bearer da250526d583edabca8ac2f99e37ee39aa02a3c076c0edc6929095e20ca18dcf'
        ), json_encode([
            'path' => '/provider',
            'scopes' => ['provider'],
            'public' => true,
            'config' => [
                'connection' => 2,
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
    }

    private function assertResponse(string $path, string $expectFile): void
    {
        $response = $this->sendRequest($path, 'GET', [
            'User-Agent'    => 'Fusio TestCase',
        ]);

        $body   = (string) $response->getBody();
        $expect = file_get_contents($expectFile);

        $this->assertEquals(200, $response->getStatusCode(), $body);
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    protected function isTransactional(): bool
    {
        return false;
    }
}

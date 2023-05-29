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

namespace Fusio\Impl\Tests\Controller;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Framework\Loader\RoutingParser\DatabaseParser;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;

/**
 * SqlEntityTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class SqlEntityTest extends ControllerDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::dropAppTables($this->connection);
    }

    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

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
    "id": 1,
    "affected": 1
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
    "id": 1,
    "affected": 1
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
    "id": 1,
    "affected": 1
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

    public static function dropAppTables(Connection $connection): void
    {
        $tableNames = [
            'app_human_0_location',
            'app_human_0_category',
            'app_human_0',
            'app_location_0',
            'app_category_0',
        ];

        foreach ($tableNames as $tableName) {
            if ($connection->createSchemaManager()->tablesExist($tableName)) {
                $connection->executeQuery('DELETE FROM ' . $tableName . ' WHERE 1=1');
            }
        }

        foreach ($tableNames as $tableName) {
            if ($connection->createSchemaManager()->tablesExist($tableName)) {
                $connection->executeQuery('DROP TABLE ' . $tableName);
            }
        }
    }
}

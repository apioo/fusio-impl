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

namespace Fusio\Impl\Tests;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Service;
use PSX\Framework\Test\Environment;

/**
 * Assert
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Assert extends \PHPUnit\Framework\Assert
{
    public static function assertAction(string $expectName, string $expectClass, string $expectConfig, ?array $expectMetadata = null)
    {
        /** @var Connection $connection */
        $connection = Environment::getService('connection');

        $sql = $connection->createQueryBuilder()
            ->select('id', 'name', 'class', 'config', 'metadata')
            ->from('fusio_action')
            ->where('name = :name')
            ->getSQL();

        $row    = $connection->fetchAssoc($sql, ['name' => $expectName]);
        $config = json_encode(Service\Action::unserializeConfig($row['config']));

        self::assertNotEmpty($row['id']);
        self::assertEquals($expectName, $row['name']);
        self::assertEquals($expectClass, $row['class']);
        self::assertJsonStringEqualsJsonString($expectConfig, $config, $config);

        if ($expectMetadata !== null) {
            self::assertJsonStringEqualsJsonString(json_encode($expectMetadata), $row['metadata'], $row['metadata']);
        }
    }

    public static function assertSchema(string $expectName, string $expectSchema, ?string $expectForm = null, ?array $expectMetadata = null)
    {
        /** @var Connection $connection */
        $connection = Environment::getService('connection');

        $sql = $connection->createQueryBuilder()
            ->select('id', 'name', 'source', 'form', 'metadata')
            ->from('fusio_schema')
            ->where('name = :name')
            ->getSQL();

        $row = $connection->fetchAssociative($sql, ['name' => $expectName]);

        self::assertNotEmpty($row['id']);
        self::assertEquals($expectName, $row['name']);
        self::assertJsonStringEqualsJsonString($expectSchema, $row['source']);

        if ($expectForm !== null) {
            self::assertJsonStringEqualsJsonString($expectForm, $row['form']);
        }

        if ($expectMetadata !== null) {
            self::assertJsonStringEqualsJsonString(json_encode($expectMetadata), $row['metadata'], $row['metadata']);
        }
    }

    public static function assertRoute(string $expectPath, array $expectScopes, array $expectConfig, ?array $expectMetadata = null)
    {
        /** @var Connection $connection */
        $connection = Environment::getService('connection');

        $sql = $connection->createQueryBuilder()
            ->select('id', 'status', 'methods', 'path', 'controller', 'metadata')
            ->from('fusio_routes')
            ->where('path = :path')
            ->getSQL();

        $route = $connection->fetchAssociative($sql, ['path' => $expectPath]);

        self::assertNotEmpty($route['id']);
        self::assertEquals(1, $route['status']);
        self::assertEquals('ANY', $route['methods']);
        self::assertEquals($expectPath, $route['path']);
        self::assertEquals(SchemaApiController::class, $route['controller']);

        if ($expectMetadata !== null) {
            self::assertJsonStringEqualsJsonString(json_encode($expectMetadata), $route['metadata'], $route['metadata']);
        }

        // check methods
        $sql = $connection->createQueryBuilder()
            ->select('id', 'route_id', 'method', 'version', 'status', 'active', 'public', 'description', 'operation_id', 'parameters', 'request', 'action', 'costs')
            ->from('fusio_routes_method')
            ->where('route_id = :route_id')
            ->orderBy('id', 'ASC')
            ->getSQL();

        $methods = $connection->fetchAllAssociative($sql, ['route_id' => $route['id']]);

        self::assertEquals(count($expectConfig), count($methods));

        foreach ($expectConfig as $index => $row) {
            self::assertEquals($row['method'], $methods[$index]['method']);
            self::assertEquals($row['version'], $methods[$index]['version']);
            self::assertEquals($row['status'], $methods[$index]['status']);
            self::assertEquals($row['active'] ? 1 : 0, $methods[$index]['active']);
            self::assertEquals($row['public'] ? 1 : 0, $methods[$index]['public']);
            self::assertEquals($row['description'], $methods[$index]['description']);
            self::assertEquals($row['operation_id'], $methods[$index]['operation_id']);
            self::assertEquals($row['parameters'], $methods[$index]['parameters'], 'Used parameters schema ' . $methods[$index]['parameters']);
            self::assertEquals($row['request'], $methods[$index]['request'], 'Used request schema ' . $methods[$index]['request']);
            self::assertEquals($row['action'], $methods[$index]['action'], 'Used action ' . $methods[$index]['action']);
            self::assertEquals($row['costs'], $methods[$index]['costs']);

            if (isset($row['responses'])) {
                // check responses
                $sql = $connection->createQueryBuilder()
                    ->select('id', 'method_id', 'code', 'response')
                    ->from('fusio_routes_response')
                    ->where('method_id = :method_id')
                    ->orderBy('code', 'ASC')
                    ->getSQL();

                $responses = $connection->fetchAllAssociative($sql, ['method_id' => $methods[$index]['id']]);

                self::assertEquals(count($row['responses']), count($responses));

                $respIndex = 0;
                foreach ($row['responses'] as $code => $resp) {
                    self::assertEquals($code, $responses[$respIndex]['code']);
                    self::assertEquals($resp, $responses[$respIndex]['response'], 'Used ' . $responses[$respIndex]['code'] . ' response ' . $responses[$respIndex]['response']);

                    $respIndex++;
                }
            }
        }

        // check scopes
        $sql = $connection->createQueryBuilder()
            ->select('s.name')
            ->from('fusio_scope_routes', 'r')
            ->innerJoin('r', 'fusio_scope', 's', 's.id = r.scope_id')
            ->where('r.route_id = :route_id')
            ->orderBy('s.id', 'ASC')
            ->getSQL();

        $result = $connection->fetchAllAssociative($sql, ['route_id' => $route['id']]);
        $scopes = [];

        foreach ($result as $row) {
            $scopes[] = $row['name'];
        }

        self::assertEquals(count($expectScopes), count($scopes), implode(', ', $scopes));
        self::assertEquals($expectScopes, $scopes);
    }
}

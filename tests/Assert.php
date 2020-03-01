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

namespace Fusio\Impl\Tests;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Controller\SchemaApiController;
use PSX\Framework\Test\Environment;
use Fusio\Impl\Service;
use PSX\Schema\SchemaInterface;

/**
 * Assert
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class Assert extends \PHPUnit\Framework\Assert
{
    public static function assertEqualsIgnoreWhitespace($expect, $actual)
    {
        $expectString = str_replace(["\r\n", "\n", "\r"], "\n", $expect);
        $actualString = str_replace(["\r\n", "\n", "\r"], "\n", $actual);

        self::assertEquals($expectString, $actualString);
    }

    public static function assertAction(string $expectName, string $expectClass, string $expectConfig)
    {
        /** @var Connection $connection */
        $connection = Environment::getService('connection');

        $sql = $connection->createQueryBuilder()
            ->select('id', 'name', 'class', 'config')
            ->from('fusio_action')
            ->where('name = :name')
            ->getSQL();

        $row    = $connection->fetchAssoc($sql, ['name' => $expectName]);
        $config = json_encode(Service\Action::unserializeConfig($row['config']));

        self::assertNotEmpty($row['id']);
        self::assertEquals($expectName, $row['name']);
        self::assertEquals($expectClass, $row['class']);
        self::assertJsonStringEqualsJsonString($expectConfig, $config, $config);
    }

    public static function assertSchema(string $expectName, string $expectSchema, ?string $expectForm = null)
    {
        /** @var Connection $connection */
        $connection = Environment::getService('connection');

        $sql = $connection->createQueryBuilder()
            ->select('id', 'name', 'source', 'cache', 'form')
            ->from('fusio_schema')
            ->where('name = :name')
            ->getSQL();

        $row = $connection->fetchAssoc($sql, ['name' => $expectName]);

        self::assertNotEmpty($row['id']);
        self::assertEquals($expectName, $row['name']);
        self::assertJsonStringEqualsJsonString($expectSchema, $row['source']);
        self::assertNotEmpty($row['cache']);

        $schema = unserialize(base64_decode($row['cache']));
        self::assertInstanceOf(SchemaInterface::class, $schema);

        if ($expectForm !== null) {
            self::assertJsonStringEqualsJsonString($expectForm, $row['form']);
        }
    }

    public static function assertRoute(string $expectPath, array $expectScopes, array $expectConfig)
    {
        /** @var Connection $connection */
        $connection = Environment::getService('connection');

        $sql = $connection->createQueryBuilder()
            ->select('id', 'status', 'methods', 'path', 'controller')
            ->from('fusio_routes')
            ->where('path = :path')
            ->getSQL();

        $route = $connection->fetchAssoc($sql, ['path' => $expectPath]);

        self::assertNotEmpty($route['id']);
        self::assertEquals(1, $route['status']);
        self::assertEquals('ANY', $route['methods']);
        self::assertEquals($expectPath, $route['path']);
        self::assertEquals(SchemaApiController::class, $route['controller']);

        // check methods
        $sql = $connection->createQueryBuilder()
            ->select('id', 'route_id', 'method', 'version', 'status', 'active', 'public', 'description', 'operation_id', 'parameters', 'request', 'action', 'costs')
            ->from('fusio_routes_method')
            ->where('route_id = :route_id')
            ->orderBy('id', 'ASC')
            ->getSQL();

        $methods = $connection->fetchAll($sql, ['route_id' => $route['id']]);

        self::assertEquals(count($expectConfig), count($methods));

        foreach ($expectConfig as $index => $row) {
            self::assertEquals($row['method'], $methods[$index]['method']);
            self::assertEquals($row['version'], $methods[$index]['version']);
            self::assertEquals($row['status'], $methods[$index]['status']);
            self::assertEquals($row['active'] ? 1 : 0, $methods[$index]['active']);
            self::assertEquals($row['public'] ? 1 : 0, $methods[$index]['public']);
            self::assertEquals($row['description'], $methods[$index]['description']);
            self::assertEquals($row['operation_id'], $methods[$index]['operation_id']);
            self::assertEquals(self::resolveId('fusio_schema', $row['parameters']), $methods[$index]['parameters'], 'Used parameters schema ' . self::resolveName('fusio_schema', $methods[$index]['parameters']));
            self::assertEquals(self::resolveId('fusio_schema', $row['request']), $methods[$index]['request'], 'Used request schema ' . self::resolveName('fusio_schema', $methods[$index]['request']));
            self::assertEquals(self::resolveId('fusio_action', $row['action']), $methods[$index]['action'], 'Used action ' . self::resolveName('fusio_action', $methods[$index]['action']));
            self::assertEquals($row['costs'], $methods[$index]['costs']);

            if (isset($row['responses'])) {
                // check responses
                $sql = $connection->createQueryBuilder()
                    ->select('id', 'method_id', 'code', 'response')
                    ->from('fusio_routes_response')
                    ->where('method_id = :method_id')
                    ->orderBy('code', 'ASC')
                    ->getSQL();

                $responses = $connection->fetchAll($sql, ['method_id' => $methods[$index]['id']]);

                self::assertEquals(count($row['responses']), count($responses));

                $respIndex = 0;
                foreach ($row['responses'] as $code => $resp) {
                    self::assertEquals($code, $responses[$respIndex]['code']);
                    self::assertEquals(self::resolveId('fusio_schema', $resp), $responses[$respIndex]['response'], 'Used ' . $responses[$respIndex]['code'] . ' response ' . self::resolveName('fusio_schema', $responses[$respIndex]['response']));

                    $respIndex++;
                }
            }

            $index++;
        }

        // check scopes
        $sql = $connection->createQueryBuilder()
            ->select('s.name')
            ->from('fusio_scope_routes', 'r')
            ->innerJoin('r', 'fusio_scope', 's', 's.id = r.scope_id')
            ->where('r.route_id = :route_id')
            ->orderBy('s.id', 'ASC')
            ->getSQL();

        $result = $connection->fetchAll($sql, ['route_id' => $route['id']]);
        $scopes = [];

        foreach ($result as $row) {
            $scopes[] = $row['name'];
        }

        self::assertEquals(count($expectScopes), count($scopes), implode(', ', $scopes));
        self::assertEquals($expectScopes, $scopes);
    }

    private static function resolveId(string $table, ?string $name)
    {
        if ($name === null) {
            return null;
        }

        /** @var Connection $connection */
        $connection = Environment::getService('connection');

        $sql = $connection->createQueryBuilder()
            ->select('id')
            ->from($table)
            ->where('name = :name')
            ->getSQL();

        $row = $connection->fetchAssoc($sql, ['name' => $name]);

        return $row['id'] ?? null;
    }

    private static function resolveName(string $table, $id)
    {
        if ($id === null) {
            return null;
        }

        /** @var Connection $connection */
        $connection = Environment::getService('connection');

        $sql = $connection->createQueryBuilder()
            ->select('name')
            ->from($table)
            ->where('id = :id')
            ->getSQL();

        $row = $connection->fetchAssoc($sql, ['id' => $id]);

        return $row['name'] ?? null;
    }
}

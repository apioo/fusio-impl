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
    public static function assertAction(Connection $connection, string $expectName, string $expectClass, string $expectConfig, ?array $expectMetadata = null): void
    {
        $sql = $connection->createQueryBuilder()
            ->select('id', 'name', 'class', 'config', 'metadata')
            ->from('fusio_action')
            ->where('name = :name')
            ->getSQL();

        $row = $connection->fetchAssociative($sql, ['name' => $expectName]);
        if (empty($row)) {
            throw new \RuntimeException('Provided action name ' . $expectName . ' does not exist');
        }

        $config = json_encode(Service\Action::unserializeConfig($row['config']));

        self::assertNotEmpty($row['id']);
        self::assertEquals($expectName, $row['name']);
        self::assertEquals($expectClass, $row['class']);
        self::assertJsonStringEqualsJsonString($expectConfig, $config, $config);

        if ($expectMetadata !== null) {
            self::assertJsonStringEqualsJsonString(json_encode($expectMetadata), $row['metadata'], $row['metadata']);
        }
    }

    public static function assertSchema(Connection $connection, string $expectName, string $expectSchema, ?string $expectForm = null, ?array $expectMetadata = null): void
    {
        $sql = $connection->createQueryBuilder()
            ->select('id', 'name', 'source', 'form', 'metadata')
            ->from('fusio_schema')
            ->where('name = :name')
            ->getSQL();

        $row = $connection->fetchAssociative($sql, ['name' => $expectName]);
        if (empty($row)) {
            throw new \RuntimeException('Provided schema name ' . $expectName . ' does not exist');
        }

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

    public static function assertOperation(Connection $connection, int $expectStability, string $expectName, string $expectHttpMethod, string $expectHttpPath, array $expectScopes, ?array $expectMetadata = null): void
    {
        $sql = $connection->createQueryBuilder()
            ->select('id', 'stability', 'name', 'http_method', 'http_path', 'metadata')
            ->from('fusio_operation')
            ->where('name = :name')
            ->getSQL();

        $operation = $connection->fetchAssociative($sql, ['name' => $expectName]);
        if (empty($operation)) {
            throw new \RuntimeException('Provided operation ' . $expectName . ' does not exist');
        }

        self::assertNotEmpty($operation['id']);
        self::assertEquals($expectStability, $operation['stability']);
        self::assertEquals($expectName, $operation['name']);
        self::assertEquals($expectHttpMethod, $operation['http_method']);
        self::assertEquals($expectHttpPath, $operation['http_path']);

        if ($expectMetadata !== null) {
            self::assertJsonStringEqualsJsonString(json_encode($expectMetadata), $operation['metadata'], $operation['metadata']);
        }

        // check scopes
        $sql = $connection->createQueryBuilder()
            ->select('s.name')
            ->from('fusio_scope_operation', 'o')
            ->innerJoin('o', 'fusio_scope', 's', 's.id = o.scope_id')
            ->where('o.operation_id = :operation_id')
            ->orderBy('s.id', 'ASC')
            ->getSQL();

        $result = $connection->fetchAllAssociative($sql, ['operation_id' => $operation['id']]);
        $scopes = [];

        foreach ($result as $row) {
            $scopes[] = $row['name'];
        }

        self::assertEquals(count($expectScopes), count($scopes), implode(', ', $scopes));
        self::assertEquals($expectScopes, $scopes);
    }
}

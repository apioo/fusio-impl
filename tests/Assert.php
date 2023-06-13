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

namespace Fusio\Impl\Tests;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Service;

/**
 * Assert
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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

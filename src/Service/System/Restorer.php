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

namespace Fusio\Impl\Service\System;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Restorer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Restorer
{
    private const TABLE_NAME = 0;
    private const TENANT_ID_COLUMN = 1;
    private const NAME_COLUMN = 2;
    private const STATUS_COLUMN = 3;
    private const ACTIVE_STATUS = 4;

    private array $config;

    public function __construct(private readonly Connection $connection, private readonly FrameworkConfig $frameworkConfig)
    {
        $this->config = $this->buildConfig();
    }

    public function getTypes(): array
    {
        return array_keys($this->config);
    }

    public function getDataForType(string $type, int $startIndex, int $count): array
    {
        if (!isset($this->config[$type])) {
            throw new StatusCode\BadRequestException('Provided an invalid type');
        }

        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $config = $this->config[$type];

        $condition = Condition::withAnd();
        $condition->equals($config[self::TENANT_ID_COLUMN], $this->frameworkConfig->getTenantId());
        $condition->notEquals($config[self::STATUS_COLUMN], $config[self::ACTIVE_STATUS]);

        $totalResults = $this->getTotalResults($config, $condition);
        $result = $this->getEntries($config, $condition, $startIndex, $count);

        $entries = [];
        foreach ($result as $row) {
            $entries[] = [
                'id' => (int) $row['id'],
                'status' => (int) $row['status'],
                'name' => $row['name'],
            ];
        }

        return [
            'totalResults' => $totalResults,
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $entries,
        ];
    }

    public function restore(string $type, ?string $id): int
    {
        if (empty($id)) {
            throw new StatusCode\BadRequestException('Provided no id');
        }

        if (!isset($this->config[$type])) {
            throw new StatusCode\BadRequestException('Provided an invalid type');
        }

        $config = $this->config[$type];

        return $this->restoreRecord(
            $id,
            $config[self::TABLE_NAME],
            $config[self::TENANT_ID_COLUMN],
            $config[self::NAME_COLUMN],
            $config[self::STATUS_COLUMN],
            $config[self::ACTIVE_STATUS]
        );
    }

    private function restoreRecord(string $id, string $table, string $tenantIdColumn, string $nameColumn, string $statusColumn, int $status): int
    {
        if (is_numeric($id)) {
            $id = (int) $id;
            $nameColumn = 'id';
        }

        return (int) $this->connection->update($table, [
            $statusColumn => $status,
        ], [
            $tenantIdColumn => $this->frameworkConfig->getTenantId(),
            $nameColumn => $id,
        ]);
    }

    private function getTotalResults(array $config, Condition $condition): int
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(['COUNT(*) AS cnt'])
            ->from($config[self::TABLE_NAME])
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        return (int) $this->connection->fetchOne($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    private function getEntries(array $config, Condition $condition, int $startIndex, int $count): array
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(['id', $config[self::STATUS_COLUMN] . ' AS status', $config[self::NAME_COLUMN] . ' AS name'])
            ->from($config[self::TABLE_NAME])
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->orderBy('id', 'DESC')
            ->setParameters($condition->getValues());

        $query = $this->connection->getDatabasePlatform()->modifyLimitQuery($queryBuilder->getSQL(), $count, $startIndex);

        return $this->connection->fetchAllAssociative($query, $queryBuilder->getParameters());
    }

    private function buildConfig(): array
    {
        return [
            'action' => [
                Table\Generated\ActionTable::NAME,
                Table\Generated\ActionTable::COLUMN_TENANT_ID,
                Table\Generated\ActionTable::COLUMN_NAME,
                Table\Generated\ActionTable::COLUMN_STATUS,
                Table\Action::STATUS_ACTIVE,
            ],
            'app' => [
                Table\Generated\AppTable::NAME,
                Table\Generated\AppTable::COLUMN_TENANT_ID,
                Table\Generated\AppTable::COLUMN_NAME,
                Table\Generated\AppTable::COLUMN_STATUS,
                Table\App::STATUS_ACTIVE,
            ],
            'connection' => [
                Table\Generated\ConnectionTable::NAME,
                Table\Generated\ConnectionTable::COLUMN_TENANT_ID,
                Table\Generated\ConnectionTable::COLUMN_NAME,
                Table\Generated\ConnectionTable::COLUMN_STATUS,
                Table\Connection::STATUS_ACTIVE,
            ],
            'cronjob' => [
                Table\Generated\CronjobTable::NAME,
                Table\Generated\CronjobTable::COLUMN_TENANT_ID,
                Table\Generated\CronjobTable::COLUMN_NAME,
                Table\Generated\CronjobTable::COLUMN_STATUS,
                Table\Cronjob::STATUS_ACTIVE,
            ],
            'event' => [
                Table\Generated\EventTable::NAME,
                Table\Generated\EventTable::COLUMN_TENANT_ID,
                Table\Generated\EventTable::COLUMN_NAME,
                Table\Generated\EventTable::COLUMN_STATUS,
                Table\Event::STATUS_ACTIVE,
            ],
            'page' => [
                Table\Generated\PageTable::NAME,
                Table\Generated\PageTable::COLUMN_TENANT_ID,
                Table\Generated\PageTable::COLUMN_TITLE,
                Table\Generated\PageTable::COLUMN_STATUS,
                Table\Page::STATUS_VISIBLE,
            ],
            'plan' => [
                Table\Generated\PlanTable::NAME,
                Table\Generated\PlanTable::COLUMN_TENANT_ID,
                Table\Generated\PlanTable::COLUMN_NAME,
                Table\Generated\PlanTable::COLUMN_STATUS,
                Table\Plan::STATUS_ACTIVE,
            ],
            'rate' => [
                Table\Generated\RateTable::NAME,
                Table\Generated\RateTable::COLUMN_TENANT_ID,
                Table\Generated\RateTable::COLUMN_NAME,
                Table\Generated\RateTable::COLUMN_STATUS,
                Table\Rate::STATUS_ACTIVE,
            ],
            'role' => [
                Table\Generated\RoleTable::NAME,
                Table\Generated\RoleTable::COLUMN_TENANT_ID,
                Table\Generated\RoleTable::COLUMN_NAME,
                Table\Generated\RoleTable::COLUMN_STATUS,
                Table\Role::STATUS_ACTIVE,
            ],
            'operation' => [
                Table\Generated\OperationTable::NAME,
                Table\Generated\OperationTable::COLUMN_TENANT_ID,
                Table\Generated\OperationTable::COLUMN_NAME,
                Table\Generated\OperationTable::COLUMN_STATUS,
                Table\Operation::STATUS_ACTIVE,
            ],
            'schema' => [
                Table\Generated\SchemaTable::NAME,
                Table\Generated\SchemaTable::COLUMN_TENANT_ID,
                Table\Generated\SchemaTable::COLUMN_NAME,
                Table\Generated\SchemaTable::COLUMN_STATUS,
                Table\Schema::STATUS_ACTIVE,
            ],
            'scope' => [
                Table\Generated\ScopeTable::NAME,
                Table\Generated\ScopeTable::COLUMN_TENANT_ID,
                Table\Generated\ScopeTable::COLUMN_NAME,
                Table\Generated\ScopeTable::COLUMN_STATUS,
                Table\Scope::STATUS_ACTIVE,
            ],
            'user' => [
                Table\Generated\UserTable::NAME,
                Table\Generated\UserTable::COLUMN_TENANT_ID,
                Table\Generated\UserTable::COLUMN_NAME,
                Table\Generated\UserTable::COLUMN_STATUS,
                Table\User::STATUS_ACTIVE,
            ],
        ];
    }
}

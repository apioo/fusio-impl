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

namespace Fusio\Impl\Service\System;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

/**
 * Restorer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Restorer
{
    private const TABLE_NAME = 0;
    private const NAME_COLUMN = 1;
    private const STATUS_COLUMN = 2;
    private const ACTIVE_STATUS = 3;

    private Connection $connection;
    private array $config;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
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

        $query = 'SELECT COUNT(*) AS cnt
                    FROM ' . $config[self::TABLE_NAME] . '
                   WHERE ' . $config[self::STATUS_COLUMN] . ' != :status';
        $totalResults = (int) $this->connection->fetchOne($query, ['status' => $config[self::ACTIVE_STATUS]]);

        $columns = [
            'id',
            $config[self::STATUS_COLUMN] . ' AS status',
            $config[self::NAME_COLUMN] . ' AS name',
        ];

        $query = 'SELECT ' . implode(', ', $columns) . '
                    FROM ' . $config[self::TABLE_NAME] . '
                   WHERE ' . $config[self::STATUS_COLUMN] . ' != :status
                ORDER BY id DESC';
        $query = $this->connection->getDatabasePlatform()->modifyLimitQuery($query, $count, $startIndex);
        $result = $this->connection->fetchAllAssociative($query, ['status' => $config[self::ACTIVE_STATUS]]);

        return [
            'totalResults' => $totalResults,
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $result,
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
            $config[self::NAME_COLUMN],
            $config[self::STATUS_COLUMN],
            $config[self::ACTIVE_STATUS]
        );
    }

    private function restoreRecord(string $id, string $table, string $nameColumn, string $statusColumn, int $status): int
    {
        if (is_numeric($id)) {
            $id = (int) $id;
            $nameColumn = 'id';
        }

        return (int) $this->connection->update($table, [
            $statusColumn => $status,
        ], [
            $nameColumn => $id
        ]);
    }

    public function buildConfig(): array
    {
        return [
            'action' => [
                Table\Generated\ActionTable::NAME,
                Table\Generated\ActionTable::COLUMN_NAME,
                Table\Generated\ActionTable::COLUMN_STATUS,
                Table\Action::STATUS_ACTIVE,
            ],
            'app' => [
                Table\Generated\AppTable::NAME,
                Table\Generated\AppTable::COLUMN_NAME,
                Table\Generated\AppTable::COLUMN_STATUS,
                Table\App::STATUS_ACTIVE,
            ],
            'connection' => [
                Table\Generated\ConnectionTable::NAME,
                Table\Generated\ConnectionTable::COLUMN_NAME,
                Table\Generated\ConnectionTable::COLUMN_STATUS,
                Table\Connection::STATUS_ACTIVE,
            ],
            'cronjob' => [
                Table\Generated\CronjobTable::NAME,
                Table\Generated\CronjobTable::COLUMN_NAME,
                Table\Generated\CronjobTable::COLUMN_STATUS,
                Table\Cronjob::STATUS_ACTIVE,
            ],
            'event' => [
                Table\Generated\EventTable::NAME,
                Table\Generated\EventTable::COLUMN_NAME,
                Table\Generated\EventTable::COLUMN_STATUS,
                Table\Event::STATUS_ACTIVE,
            ],
            'page' => [
                Table\Generated\PageTable::NAME,
                Table\Generated\PageTable::COLUMN_TITLE,
                Table\Generated\PageTable::COLUMN_STATUS,
                Table\Page::STATUS_VISIBLE,
            ],
            'plan' => [
                Table\Generated\PlanTable::NAME,
                Table\Generated\PlanTable::COLUMN_NAME,
                Table\Generated\PlanTable::COLUMN_STATUS,
                Table\Plan::STATUS_ACTIVE,
            ],
            'rate' => [
                Table\Generated\RateTable::NAME,
                Table\Generated\RateTable::COLUMN_NAME,
                Table\Generated\RateTable::COLUMN_STATUS,
                Table\Rate::STATUS_ACTIVE,
            ],
            'role' => [
                Table\Generated\RoleTable::NAME,
                Table\Generated\RoleTable::COLUMN_NAME,
                Table\Generated\RoleTable::COLUMN_STATUS,
                Table\Role::STATUS_ACTIVE,
            ],
            'routes' => [
                Table\Generated\RoutesTable::NAME,
                Table\Generated\RoutesTable::COLUMN_PATH,
                Table\Generated\RoutesTable::COLUMN_STATUS,
                Table\Route::STATUS_ACTIVE,
            ],
            'schema' => [
                Table\Generated\SchemaTable::NAME,
                Table\Generated\SchemaTable::COLUMN_NAME,
                Table\Generated\SchemaTable::COLUMN_STATUS,
                Table\Schema::STATUS_ACTIVE,
            ],
            'scope' => [
                Table\Generated\ScopeTable::NAME,
                Table\Generated\ScopeTable::COLUMN_NAME,
                Table\Generated\ScopeTable::COLUMN_STATUS,
                Table\Scope::STATUS_ACTIVE,
            ],
            'user' => [
                Table\Generated\UserTable::NAME,
                Table\Generated\UserTable::COLUMN_NAME,
                Table\Generated\UserTable::COLUMN_STATUS,
                Table\User::STATUS_ACTIVE,
            ],
        ];
    }
}

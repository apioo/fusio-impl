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

namespace Fusio\Impl\Repository;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Model;
use Fusio\Engine\Repository;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Sql\Condition;

/**
 * UserDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UserDatabase implements Repository\UserInterface
{
    private Connection $connection;
    private FrameworkConfig $frameworkConfig;

    public function __construct(Connection $connection, FrameworkConfig $frameworkConfig)
    {
        $this->connection = $connection;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function getAll(): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\UserTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\UserTable::COLUMN_STATUS, Table\User::STATUS_ACTIVE);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\UserTable::COLUMN_ID,
                Table\Generated\UserTable::COLUMN_ROLE_ID,
                Table\Generated\UserTable::COLUMN_PLAN_ID,
                Table\Generated\UserTable::COLUMN_STATUS,
                Table\Generated\UserTable::COLUMN_EXTERNAL_ID,
                Table\Generated\UserTable::COLUMN_NAME,
                Table\Generated\UserTable::COLUMN_EMAIL,
                Table\Generated\UserTable::COLUMN_POINTS,
                Table\Generated\UserTable::COLUMN_METADATA,
            ])
            ->from('fusio_user', 'usr')
            ->orderBy(Table\Generated\UserTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $result = $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        $users = [];
        foreach ($result as $row) {
            $users[] = $this->newUser($row);
        }

        return $users;
    }

    public function get(string|int $id): ?Model\UserInterface
    {
        if (empty($id)) {
            return null;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\UserTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\UserTable::COLUMN_ID, $id);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\UserTable::COLUMN_ID,
                Table\Generated\UserTable::COLUMN_ROLE_ID,
                Table\Generated\UserTable::COLUMN_PLAN_ID,
                Table\Generated\UserTable::COLUMN_STATUS,
                Table\Generated\UserTable::COLUMN_EXTERNAL_ID,
                Table\Generated\UserTable::COLUMN_NAME,
                Table\Generated\UserTable::COLUMN_EMAIL,
                Table\Generated\UserTable::COLUMN_POINTS,
                Table\Generated\UserTable::COLUMN_METADATA,
            ])
            ->from('fusio_user', 'usr')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $row = $this->connection->fetchAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        if (!empty($row)) {
            return $this->newUser($row);
        } else {
            return null;
        }
    }

    private function newUser(array $row): Model\UserInterface
    {
        $metadata = null;
        if (!empty($row[Table\Generated\UserTable::COLUMN_METADATA])) {
            $metadata = json_decode($row[Table\Generated\UserTable::COLUMN_METADATA]);
            if (!$metadata instanceof \stdClass) {
                $metadata = null;
            }
        }

        return new Model\User(
            false,
            $row[Table\Generated\UserTable::COLUMN_ID],
            $row[Table\Generated\UserTable::COLUMN_ROLE_ID],
            $this->getCategoryForRole($row[Table\Generated\UserTable::COLUMN_ROLE_ID]),
            $row[Table\Generated\UserTable::COLUMN_STATUS],
            $row[Table\Generated\UserTable::COLUMN_NAME],
            $row[Table\Generated\UserTable::COLUMN_EMAIL] ?? '',
            $row[Table\Generated\UserTable::COLUMN_POINTS] ?? 0,
            $row[Table\Generated\UserTable::COLUMN_EXTERNAL_ID] ?? null,
            $row[Table\Generated\UserTable::COLUMN_PLAN_ID] ?? null,
            $metadata
        );
    }

    private function getCategoryForRole($roleId): int
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\RoleTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\RoleTable::COLUMN_ID, $roleId);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\RoleTable::COLUMN_CATEGORY_ID,
            ])
            ->from('fusio_role', 'rol')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $categoryId = $this->connection->fetchOne($queryBuilder->getSQL(), $queryBuilder->getParameters());
        if (empty($categoryId)) {
            return 1;
        }

        return (int) $categoryId;
    }
}

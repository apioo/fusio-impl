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

namespace Fusio\Impl\Repository;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Model;
use Fusio\Engine\Repository;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Sql\Condition;

/**
 * AppDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AppDatabase implements Repository\AppInterface
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
        $condition->equals(Table\Generated\AppTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\AppTable::COLUMN_ID,
                Table\Generated\AppTable::COLUMN_USER_ID,
                Table\Generated\AppTable::COLUMN_STATUS,
                Table\Generated\AppTable::COLUMN_NAME,
                Table\Generated\AppTable::COLUMN_URL,
                Table\Generated\AppTable::COLUMN_PARAMETERS,
                Table\Generated\AppTable::COLUMN_APP_KEY,
                Table\Generated\AppTable::COLUMN_METADATA,
            ])
            ->from('fusio_app', 'app')
            ->orderBy(Table\Generated\AppTable::COLUMN_ID, 'DESC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $result = $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        $apps = [];
        foreach ($result as $row) {
            $apps[] = $this->newApp($row, []);
        }

        return $apps;
    }

    public function get(string|int $id): ?Model\AppInterface
    {
        if (empty($id)) {
            return null;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\AppTable::COLUMN_ID, $id);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\AppTable::COLUMN_ID,
                Table\Generated\AppTable::COLUMN_USER_ID,
                Table\Generated\AppTable::COLUMN_STATUS,
                Table\Generated\AppTable::COLUMN_NAME,
                Table\Generated\AppTable::COLUMN_URL,
                Table\Generated\AppTable::COLUMN_PARAMETERS,
                Table\Generated\AppTable::COLUMN_APP_KEY,
                Table\Generated\AppTable::COLUMN_METADATA,
            ])
            ->from('fusio_app', 'app')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $row = $this->connection->fetchAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        if (!empty($row)) {
            return $this->newApp($row, $this->getScopes($row[Table\Generated\AppTable::COLUMN_ID]));
        } else {
            return null;
        }
    }

    protected function getScopes(string|int $appId): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\ScopeTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\AppScopeTable::COLUMN_APP_ID, $appId);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'scope.' . Table\Generated\ScopeTable::COLUMN_NAME,
            ])
            ->from('fusio_app_scope', 'app_scope')
            ->innerJoin('app_scope', 'fusio_scope', 'scope', 'app_scope.' . Table\Generated\AppScopeTable::COLUMN_SCOPE_ID . ' = scope.' . Table\Generated\ScopeTable::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $result = $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        $names = [];
        foreach ($result as $row) {
            $names[] = $row['name'];
        }

        return $names;
    }

    protected function newApp(array $row, array $scopes): Model\AppInterface
    {
        $parameters = [];
        if (!empty($row[Table\Generated\AppTable::COLUMN_PARAMETERS])) {
            parse_str($row[Table\Generated\AppTable::COLUMN_PARAMETERS], $parameters);
        }

        $metadata = null;
        if (!empty($row[Table\Generated\AppTable::COLUMN_METADATA])) {
            $metadata = json_decode($row[Table\Generated\AppTable::COLUMN_METADATA]);
            if (!$metadata instanceof \stdClass) {
                $metadata = null;
            }
        }

        return new Model\App(
            false,
            $row[Table\Generated\AppTable::COLUMN_ID],
            $row[Table\Generated\AppTable::COLUMN_USER_ID],
            $row[Table\Generated\AppTable::COLUMN_STATUS],
            $row[Table\Generated\AppTable::COLUMN_NAME],
            $row[Table\Generated\AppTable::COLUMN_URL],
            $row[Table\Generated\AppTable::COLUMN_APP_KEY],
            $parameters,
            $scopes,
            $metadata
        );
    }
}

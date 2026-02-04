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
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Sql\Condition;

/**
 * ActionDatabase
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ActionDatabase implements Repository\ActionInterface
{
    private Connection $connection;
    private Service\System\FrameworkConfig $frameworkConfig;
    private bool $async = true;

    public function __construct(Connection $connection, Service\System\FrameworkConfig $frameworkConfig)
    {
        $this->connection = $connection;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function getAll(): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\ActionTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\ActionTable::COLUMN_STATUS, Table\Action::STATUS_ACTIVE);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\ActionTable::COLUMN_ID,
                Table\Generated\ActionTable::COLUMN_NAME,
                Table\Generated\ActionTable::COLUMN_CLASS,
                Table\Generated\ActionTable::COLUMN_ASYNC,
                Table\Generated\ActionTable::COLUMN_CONFIG,
                Table\Generated\ActionTable::COLUMN_DATE,
                Table\Generated\ActionTable::COLUMN_METADATA,
            ])
            ->from('fusio_action', 'action')
            ->orderBy(Table\Generated\ActionTable::COLUMN_NAME, 'ASC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $result = $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        $actions = [];
        foreach ($result as $row) {
            $actions[] = $this->newAction($row);
        }

        return $actions;
    }

    public function get(string|int $id): ?Model\ActionInterface
    {
        if (empty($id)) {
            return null;
        }

        $hash = null;
        if (is_numeric($id)) {
            $column = Table\Generated\ActionTable::COLUMN_ID;
        } else {
            $column = Table\Generated\ActionTable::COLUMN_NAME;

            if (str_contains($id, '@')) {
                $hash = substr(strstr($id, '@'), 1);
                $id = strstr($id, '@', true);
            }
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\ActionTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals($column, $id);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\ActionTable::COLUMN_ID,
                Table\Generated\ActionTable::COLUMN_NAME,
                Table\Generated\ActionTable::COLUMN_CLASS,
                Table\Generated\ActionTable::COLUMN_ASYNC,
                Table\Generated\ActionTable::COLUMN_CONFIG,
                Table\Generated\ActionTable::COLUMN_DATE,
                Table\Generated\ActionTable::COLUMN_METADATA,
            ])
            ->from('fusio_action', 'action')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $row = $this->connection->fetchAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());

        if (!empty($row)) {
            if ($hash !== null) {
                $config = $this->resolveConfigByHash((int) $row[Table\Generated\ActionTable::COLUMN_ID], $hash);
                if ($config !== null) {
                    $row[Table\Generated\ActionTable::COLUMN_CONFIG] = $config;
                }
            }

            return $this->newAction($row);
        } else {
            return null;
        }
    }

    public function setAsync(bool $async): void
    {
        $this->async = $async;
    }

    private function newAction(array $row): Model\ActionInterface
    {
        $config = !empty($row[Table\Generated\ActionTable::COLUMN_CONFIG]) ? Service\Action::unserializeConfig($row[Table\Generated\ActionTable::COLUMN_CONFIG]) : [];

        $metadata = null;
        if (!empty($row[Table\Generated\ActionTable::COLUMN_METADATA])) {
            $metadata = json_decode($row[Table\Generated\ActionTable::COLUMN_METADATA]);
            if (!$metadata instanceof \stdClass) {
                $metadata = null;
            }
        }

        return new Model\Action(
            $row[Table\Generated\ActionTable::COLUMN_ID],
            $row[Table\Generated\ActionTable::COLUMN_NAME],
            $row[Table\Generated\ActionTable::COLUMN_CLASS],
            $this->async ? (bool) $row[Table\Generated\ActionTable::COLUMN_ASYNC] : false,
            $config ?? [],
            $metadata
        );
    }

    private function resolveConfigByHash(int $actionId, string $hash): ?string
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\ActionCommitTable::COLUMN_ACTION_ID, $actionId);
        $condition->equals(Table\Generated\ActionCommitTable::COLUMN_COMMIT_HASH, $hash);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\ActionCommitTable::COLUMN_CONFIG,
            ])
            ->from('fusio_action_commit', 'action_commit')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $config = $this->connection->fetchOne($queryBuilder->getSQL(), $queryBuilder->getParameters());

        return !empty($config) ? $config : null;
    }
}

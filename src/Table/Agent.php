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

namespace Fusio\Impl\Table;

use Fusio\Impl\Table\Generated\AgentRow;
use PSX\Sql\Condition;

/**
 * Agent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Agent extends Generated\AgentTable
{
    public const STATUS_ACTIVE  = 1;

    public const STATUS_DELETED = 0;

    public const TYPE_GENERAL = 0;
    
    public const TYPE_ARCHITECT = 1;
    
    public const TYPE_ACTION = 2;
    
    public const TYPE_SCHEMA = 3;
    
    public const TYPE_DATABASE = 4;

    public const TYPE_SEED = 5;

    public function findOneByIdentifier(?string $tenantId, int $categoryId, string $id): ?AgentRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByTenantAndName($tenantId, $categoryId, urldecode(substr($id, 1)));
        } else {
            return $this->findOneByTenantAndId($tenantId, $categoryId, (int) $id);
        }
    }

    public function findOneByTenantAndId(?string $tenantId, ?int $categoryId, int $id): ?AgentRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        if ($categoryId !== null) {
            $condition->equals(self::COLUMN_CATEGORY_ID, $categoryId);
        }
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndName(?string $tenantId, ?int $categoryId, string $name): ?AgentRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        if ($categoryId !== null) {
            $condition->equals(self::COLUMN_CATEGORY_ID, $categoryId);
        }
        
        $condition->equals(self::COLUMN_NAME, $name);

        return $this->findOneBy($condition);
    }

    /**
     * We create the Fusio default agents with 1 as connection, which is not an actual agent connection but the system
     * connection, we do this since we have no actual agent connection. If a user creates the first agent connection we
     * internally update the connection id to use the fitting connection
     */
    public function replaceDefaultConnection(int $connectionId): void
    {
        $this->connection->update(self::NAME, [
            self::COLUMN_CONNECTION_ID => $connectionId
        ], [
            self::COLUMN_CONNECTION_ID => null
        ]);
    }
}

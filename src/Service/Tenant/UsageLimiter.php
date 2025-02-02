<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Service\Tenant;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Exception\UsageLimitExceededException;
use Fusio\Impl\Table;

/**
 * UsageLimiter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UsageLimiter
{
    private Connection $connection;
    private LimiterInterface $limiter;

    public function __construct(Connection $connection, LimiterInterface $limiter)
    {
        $this->connection = $connection;
        $this->limiter = $limiter;
    }

    public function assertActionCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\ActionTable::NAME, $tenantId),
            $this->limiter->getActionCount(),
            'action'
        );
    }

    public function assertAppCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\AppTable::NAME, $tenantId),
            $this->limiter->getAppCount(),
            'app'
        );
    }

    public function assertCategoryCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\CategoryTable::NAME, $tenantId),
            $this->limiter->getCategoryCount(),
            'category'
        );
    }

    public function assertConnectionCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\ConnectionTable::NAME, $tenantId),
            $this->limiter->getConnectionCount(),
            'connection'
        );
    }

    public function assertCronjobCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\CronjobTable::NAME, $tenantId),
            $this->limiter->getCronjobCount(),
            'cronjob'
        );
    }

    public function assertEventCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\EventTable::NAME, $tenantId),
            $this->limiter->getEventCount(),
            'event'
        );
    }

    public function assertIdentityCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\IdentityTable::NAME, $tenantId),
            $this->limiter->getIdentityCount(),
            'identity'
        );
    }

    public function assertOperationCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\OperationTable::NAME, $tenantId),
            $this->limiter->getOperationCount(),
            'operation'
        );
    }

    public function assertPageCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\PageTable::NAME, $tenantId),
            $this->limiter->getPageCount(),
            'page'
        );
    }

    public function assertPlanCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\PlanTable::NAME, $tenantId),
            $this->limiter->getPlanCount(),
            'plan'
        );
    }

    public function assertRateCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\RateTable::NAME, $tenantId),
            $this->limiter->getRateCount(),
            'rate'
        );
    }

    public function assertRoleCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\RoleTable::NAME, $tenantId),
            $this->limiter->getRoleCount(),
            'role'
        );
    }

    public function assertSchemaCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\SchemaTable::NAME, $tenantId),
            $this->limiter->getSchemaCount(),
            'schema'
        );
    }

    public function assertScopeCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\ScopeTable::NAME, $tenantId),
            $this->limiter->getScopeCount(),
            'scope'
        );
    }

    public function assertUserCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\UserTable::NAME, $tenantId),
            $this->limiter->getUserCount(),
            'user'
        );
    }

    public function assertWebhookCount(?string $tenantId): void
    {
        $this->assert(
            $this->getActualCount(Table\Generated\WebhookTable::NAME, $tenantId),
            $this->limiter->getWebhookCount(),
            'webhook'
        );
    }

    private function assert(int $actual, int $expect, string $type): void
    {
        if ($actual >= $expect) {
            throw new UsageLimitExceededException('Usage limit of ' . $expect . ' exceeded for resource ' . $type);
        }
    }

    private function getActualCount(string $tableName, ?string $tenantId): int
    {
        if ($tenantId === null) {
            return 0;
        }

        return (int) $this->connection->fetchOne('SELECT COUNT(id) AS cnt FROM ' . $tableName . ' WHERE tenant_id = :tenant_id', [
            'tenant_id' => $tenantId
        ]);
    }
}

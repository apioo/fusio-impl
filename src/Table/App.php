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

use Fusio\Impl\Table\Generated\ActionRow;
use Fusio\Impl\Table\Generated\AppRow;
use Fusio\Impl\Table\Generated\OperationRow;
use PSX\Sql\Condition;

/**
 * App
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class App extends Generated\AppTable
{
    public const STATUS_ACTIVE      = 0x1;
    public const STATUS_PENDING     = 0x2;
    public const STATUS_DEACTIVATED = 0x3;
    public const STATUS_DELETED     = 0x4;

    public function findOneByIdentifier(?string $tenantId, string $id): ?AppRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByTenantAndName($tenantId, urldecode(substr($id, 1)));
        } else {
            return $this->findOneByTenantAndId($tenantId, (int) $id);
        }
    }

    public function findOneByTenantAndId(?string $tenantId, int $id): ?AppRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndName(?string $tenantId, string $name): ?AppRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_NAME, $name);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndAppKey(?string $tenantId, string $appKey): ?AppRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_APP_KEY, $appKey);

        return $this->findOneBy($condition);
    }

    public function findOneByAppKeyAndSecret(?string $tenantId, string $appKey, string $appSecret): ?AppRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_APP_KEY, $appKey);
        $condition->equals(self::COLUMN_APP_SECRET, $appSecret);
        $condition->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);

        return $this->findOneBy($condition);
    }

    public function getUserId(?string $tenantId, int $appId): int
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_ID, $appId);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'app.' . self::COLUMN_USER_ID,
            ])
            ->from('fusio_app', 'app')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        return (int) $this->connection->fetchOne($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    public function getCountForUser(?string $tenantId, int $userId): int
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_USER_ID, $userId);

        return $this->getCount($condition);
    }
}

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

namespace Fusio\Impl\Table;

use Fusio\Impl\Table\Generated\ScopeRow;
use Fusio\Impl\Table\Generated\WebhookRow;
use PSX\Sql\Condition;

/**
 * Subscription
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Webhook extends Generated\WebhookTable
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public function findOneByIdentifier(?string $tenantId, string $id): ?WebhookRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByTenantAndName($tenantId, urldecode(substr($id, 1)));
        } else {
            return $this->findOneByTenantAndId($tenantId, (int) $id);
        }
    }

    public function findOneByTenantAndId(?string $tenantId, int $id): ?WebhookRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndName(?string $tenantId, string $name): ?WebhookRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_NAME, $name);

        return $this->findOneBy($condition);
    }

    public function getWebhooksForEvent(int $eventId): array
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_EVENT_ID, $eventId);
        $condition->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'webhook.' . self::COLUMN_ID,
                'webhook.' . self::COLUMN_ENDPOINT,
            ])
            ->from('fusio_webhook', 'webhook')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->orderBy('webhook.' . self::COLUMN_ID, 'ASC')
            ->setParameters($condition->getValues());

        return $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    public function getCountForUser(?string $tenantId, int $userId): int
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_USER_ID, $userId);

        return $this->getCount($condition);
    }

    public function deleteAllResponses(int $webhookId): void
    {
        $this->connection->executeStatement('DELETE FROM fusio_webhook_response WHERE webhook_id = :webhook_id', [
            'webhook_id' => $webhookId
        ]);
    }
}

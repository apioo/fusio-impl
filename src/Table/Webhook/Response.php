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

namespace Fusio\Impl\Table\Webhook;

use Fusio\Impl\Table\Generated;
use PSX\Sql\Condition;

/**
 * Response
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Response extends Generated\WebhookResponseTable
{
    public const STATUS_PENDING = 1;
    public const STATUS_DONE = 2;
    public const STATUS_EXCEEDED = 3;

    public function getAllByWebhook(int $webhookId): array
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_WEBHOOK_ID, $webhookId);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'response.' . self::COLUMN_ID,
                'response.' . self::COLUMN_STATUS,
                'response.' . self::COLUMN_ATTEMPTS,
                'response.' . self::COLUMN_CODE,
                'response.' . self::COLUMN_BODY,
                'response.' . self::COLUMN_EXECUTE_DATE,
            ])
            ->from('fusio_webhook_response', 'response')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->orderBy('response.' . self::COLUMN_EXECUTE_DATE, 'DESC')
            ->orderBy('response.' . self::COLUMN_ID, 'ASC')
            ->setParameters($condition->getValues())
            ->setFirstResult(0)
            ->setMaxResults(8);

        return $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }
}

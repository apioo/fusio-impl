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

namespace Fusio\Impl\Table\Agent;

use Fusio\Impl\Table\Generated;
use PSX\DateTime\LocalDateTime;
use PSX\Json\Parser;
use PSX\Sql\Condition;

/**
 * Task
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Task extends Generated\AgentTaskTable
{
    public const STATUS_PENDING   = 1;

    public const STATUS_RUNNING   = 2;

    public const STATUS_COMPLETED = 3;

    public const STATUS_FAILED    = 4;

    public const STATUS_CANCELED  = 5;

    private const DATE_FORMAT = 'Y-m-d H:i:s';

    public static function getStatusName(int $status): string
    {
        return match ($status) {
            self::STATUS_RUNNING => 'running',
            self::STATUS_COMPLETED => 'completed',
            self::STATUS_FAILED => 'failed',
            self::STATUS_CANCELED => 'canceled',
            default => 'pending',
        };
    }

    public static function isFinished(int $status): bool
    {
        return in_array($status, [self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELED], true);
    }

    public function findOneByTenantAndId(?string $tenantId, int $agentId, int $userId, int $id): ?Generated\AgentTaskRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_AGENT_ID, $agentId);
        $condition->equals(self::COLUMN_USER_ID, $userId);
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }

    /**
     * Returns the current status directly from the database, this is used by long-running executions to detect whether
     * a task was canceled in the meantime
     */
    public function getCurrentStatus(int $taskId): ?int
    {
        $status = $this->connection->fetchOne('SELECT ' . self::COLUMN_STATUS . ' FROM ' . self::NAME . ' WHERE ' . self::COLUMN_ID . ' = :id', [
            'id' => $taskId,
        ]);

        return $status !== false ? (int) $status : null;
    }

    /**
     * Appends a step to the step log of the task, we only update the steps column so that a parallel status change
     * i.e. a cancel request is not overwritten
     */
    public function addStep(int $taskId, string $name, bool $success, ?string $message = null): void
    {
        $steps = $this->connection->fetchOne('SELECT ' . self::COLUMN_STEPS . ' FROM ' . self::NAME . ' WHERE ' . self::COLUMN_ID . ' = :id', [
            'id' => $taskId,
        ]);

        $result = [];
        if (is_string($steps) && $steps !== '') {
            $result = array_values(Parser::decodeAsArray($steps));
        }

        $result[] = [
            'name' => $name,
            'success' => $success,
            'message' => $message,
            'insertDate' => LocalDateTime::now()->toDateTime()->format(\DateTimeInterface::RFC3339),
        ];

        $this->connection->update(self::NAME, [self::COLUMN_STEPS => Parser::encode($result)], [self::COLUMN_ID => $taskId]);
    }

    /**
     * Marks the task as running, returns false in case the task is not pending anymore i.e. it was canceled before a
     * worker has picked up the task
     */
    public function markRunning(int $taskId): bool
    {
        $affected = $this->connection->update(self::NAME, [
            self::COLUMN_STATUS => self::STATUS_RUNNING,
            self::COLUMN_START_DATE => LocalDateTime::now()->toDateTime()->format(self::DATE_FORMAT),
        ], [
            self::COLUMN_ID => $taskId,
            self::COLUMN_STATUS => self::STATUS_PENDING,
        ]);

        return $affected > 0;
    }

    /**
     * Finishes the task with the provided status, a task which was canceled in the meantime keeps its status
     */
    public function markFinished(int $taskId, int $status, ?string $message): void
    {
        $this->connection->update(self::NAME, [
            self::COLUMN_STATUS => $status,
            self::COLUMN_MESSAGE => $message,
            self::COLUMN_END_DATE => LocalDateTime::now()->toDateTime()->format(self::DATE_FORMAT),
        ], [
            self::COLUMN_ID => $taskId,
            self::COLUMN_STATUS => self::STATUS_RUNNING,
        ]);
    }

    /**
     * Cancels a pending or running task, returns false in case the task was already finished
     */
    public function markCanceled(int $taskId): bool
    {
        $affected = $this->connection->executeStatement('UPDATE ' . self::NAME . ' SET ' . self::COLUMN_STATUS . ' = :canceled, ' . self::COLUMN_END_DATE . ' = :end_date WHERE ' . self::COLUMN_ID . ' = :id AND ' . self::COLUMN_STATUS . ' IN (:pending, :running)', [
            'canceled' => self::STATUS_CANCELED,
            'end_date' => LocalDateTime::now()->toDateTime()->format(self::DATE_FORMAT),
            'id' => $taskId,
            'pending' => self::STATUS_PENDING,
            'running' => self::STATUS_RUNNING,
        ]);

        return $affected > 0;
    }
}

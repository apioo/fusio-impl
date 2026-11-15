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

namespace Fusio\Impl\Service\Agent\Task;

use Fusio\Impl\Table;

/**
 * Writes the steps of an agent task and allows a long-running execution to detect whether the task was canceled. If
 * an executor is invoked outside a task (no task id) all calls are no-ops, so executors can be reused everywhere
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class StepLogger
{
    public function __construct(private Table\Agent\Task $taskTable, private ?int $taskId = null)
    {
    }

    public function log(string $name, bool $success, ?string $message = null): void
    {
        if (empty($this->taskId)) {
            return;
        }

        $this->taskTable->addStep($this->taskId, $name, $success, $message);
    }

    public function isCanceled(): bool
    {
        if (empty($this->taskId)) {
            return false;
        }

        return $this->taskTable->getCurrentStatus($this->taskId) === Table\Agent\Task::STATUS_CANCELED;
    }
}

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

use Fusio\Engine\ContextInterface;
use Fusio\Engine\Model\Agent;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\Request;
use Fusio\Impl\Table;
use Fusio\Model\Agent\Item;
use Fusio\Model\Agent\ItemObject;
use Fusio\Model\Agent\ItemText;
use PSX\Record\RecordableInterface;
use PSX\Schema\ObjectMapperInterface;
use PSX\Schema\SchemaSource;
use Throwable;

/**
 * Executes an agent task, this means we invoke the action of the agent with the item of the task as payload. The
 * action receives the following arguments: "agentId", "taskId" and "connectionId". The action can return a message
 * object (success and message) which is used as result of the task
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Executor
{
    public function __construct(
        private Table\Agent\Task $taskTable,
        private Table\Agent $agentTable,
        private ProcessorInterface $processor,
        private ObjectMapperInterface $objectMapper,
    ) {
    }

    public function execute(int $taskId, ContextInterface $context): void
    {
        $task = $this->taskTable->find($taskId);
        if (!$task instanceof Table\Generated\AgentTaskRow) {
            return;
        }

        if (!$this->taskTable->markRunning($taskId)) {
            // the task is not pending anymore i.e. it was canceled before a worker has picked it up
            return;
        }

        // in case the messenger uses a sync transport the task is executed inside the HTTP request
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        try {
            [$success, $message] = $this->invoke($task, $context);
        } catch (Throwable $e) {
            $success = false;
            $message = $e->getMessage();

            $this->taskTable->addStep($taskId, 'Task failed', false, $message);
        }

        $this->taskTable->markFinished($taskId, $success ? Table\Agent\Task::STATUS_COMPLETED : Table\Agent\Task::STATUS_FAILED, $message);
    }

    /**
     * @return array{bool, string|null}
     */
    private function invoke(Table\Generated\AgentTaskRow $task, ContextInterface $context): array
    {
        $agent = $this->agentTable->find($task->getAgentId());
        if (!$agent instanceof Table\Generated\AgentRow) {
            return [false, 'The agent of this task does not exist anymore'];
        }

        $action = $agent->getAction();
        if (empty($action)) {
            return [false, 'The agent has no action configured'];
        }

        $item = $this->objectMapper->readJson($task->getItem(), SchemaSource::fromClass(Item::class));

        $arguments = [
            'agentId' => $agent->getId(),
            'taskId' => $task->getId(),
            'connectionId' => $task->getConnectionId(),
        ];

        $request = new Request($arguments, $this->getPayload($item), new Request\AgentRequestContext(new Agent($agent->getId(), $agent->getName(), $agent->getDescription())));

        $response = $this->processor->execute($action, $request, $context, false);

        // the processor can not extract protected properties, so we convert models i.e. a message model to a record
        if ($response instanceof RecordableInterface) {
            $response = $response->toRecord();
        } elseif ($response instanceof \JsonSerializable) {
            $response = $response->jsonSerialize();
        }

        $result = $this->processor->extract($response);

        $success = $result?->get('success');
        $message = $result?->get('message');

        return [
            $success !== false,
            is_string($message) ? $message : null,
        ];
    }

    private function getPayload(mixed $item): mixed
    {
        if ($item instanceof ItemObject) {
            return $item->getPayload();
        } elseif ($item instanceof ItemText) {
            return $item->getContent();
        }

        return $item;
    }
}

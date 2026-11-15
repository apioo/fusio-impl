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

namespace Fusio\Impl\Service\Agent;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Messenger\ExecuteAgentTask;
use Fusio\Impl\Service\Agent\Task\StepLogger;
use Fusio\Impl\Table;
use Fusio\Model\Backend\AgentTaskCreate;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Creates agent tasks which execute the action of an agent in the background
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Task
{
    public function __construct(
        private Table\Agent $agentTable,
        private Table\Agent\Task $taskTable,
        private Table\Connection $connectionTable,
        private MessageBusInterface $messageBus,
        private ContextFactory $contextFactory,
    ) {
    }

    public function create(string $agentId, AgentTaskCreate $task, ?string $connection, ContextInterface $context): int
    {
        $agent = $this->agentTable->findOneByIdentifier($context->getTenantId(), $context->getUser()->getCategoryId(), $agentId);
        if (!$agent instanceof Table\Generated\AgentRow) {
            throw new StatusCode\NotFoundException('Could not find agent');
        }

        if ($agent->getStatus() == Table\Agent::STATUS_DELETED) {
            throw new StatusCode\GoneException('Agent was deleted');
        }

        if (empty($agent->getAction())) {
            throw new StatusCode\BadRequestException('The agent has no action configured, an agent needs an action in order to execute a task');
        }

        $item = $task->getItem();
        if ($item === null) {
            throw new StatusCode\BadRequestException('Provided no item');
        }

        $connectionId = null;
        if (!empty($connection)) {
            $row = $this->connectionTable->findOneByIdentifier($context->getTenantId(), $context->getUser()->getCategoryId(), $connection);
            if (!$row instanceof Table\Generated\ConnectionRow || $row->getStatus() !== Table\Connection::STATUS_ACTIVE) {
                throw new StatusCode\BadRequestException('Provided connection does not exist');
            }

            $connectionId = $row->getId();
        }

        $row = new Table\Generated\AgentTaskRow();
        $row->setAgentId($agent->getId());
        $row->setUserId($context->getUser()->getId());
        $row->setConnectionId($connectionId);
        $row->setStatus(Table\Agent\Task::STATUS_PENDING);
        $row->setItem(Parser::encode($item));
        $row->setInsertDate(LocalDateTime::now());
        $this->taskTable->create($row);

        $taskId = $this->taskTable->getLastInsertId();

        $this->messageBus->dispatch(new ExecuteAgentTask($taskId, $context, $this->contextFactory->getActive()));

        return $taskId;
    }

    public function cancel(string $agentId, int $taskId, ContextInterface $context): int
    {
        $agent = $this->agentTable->findOneByIdentifier($context->getTenantId(), $context->getUser()->getCategoryId(), $agentId);
        if (!$agent instanceof Table\Generated\AgentRow) {
            throw new StatusCode\NotFoundException('Could not find agent');
        }

        $task = $this->taskTable->findOneByAgentAndId($agent->getId(), $context->getUser()->getId(), $taskId);
        if (!$task instanceof Table\Generated\AgentTaskRow) {
            throw new StatusCode\NotFoundException('Could not find task');
        }

        if (!$this->taskTable->markCanceled($task->getId())) {
            throw new StatusCode\BadRequestException('The task is already finished');
        }

        $this->taskTable->addStep($task->getId(), 'Task canceled', false, 'The task was canceled by the user, steps which are already running are completed');

        return $task->getId();
    }

    /**
     * Resolves the provided agent identifier (id or ~name) to the agent id
     */
    public function resolveAgentId(string $agentId, ContextInterface $context): int
    {
        $agent = $this->agentTable->findOneByIdentifier($context->getTenantId(), $context->getUser()->getCategoryId(), $agentId);
        if (!$agent instanceof Table\Generated\AgentRow) {
            throw new StatusCode\NotFoundException('Could not find agent');
        }

        return $agent->getId();
    }

    public function newStepLogger(?int $taskId): StepLogger
    {
        return new StepLogger($this->taskTable, $taskId);
    }
}

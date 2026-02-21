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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Agent\CreatedEvent;
use Fusio\Impl\Event\Agent\DeletedEvent;
use Fusio\Impl\Event\Agent\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\AgentCreate;
use Fusio\Model\Backend\AgentUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Agent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Agent
{
    public function __construct(
        private Table\Agent $agentTable,
        private Agent\Validator $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(AgentCreate $agent, UserContext $context): int
    {
        $this->validator->assert($agent, $context->getCategoryId(), $context->getTenantId());

        // create agent
        try {
            $this->agentTable->beginTransaction();

            $row = new Table\Generated\AgentRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setStatus(Table\Agent::STATUS_ACTIVE);
            $row->setName($agent->getName());
            $row->setDescription($agent->getDescription());
            $row->setIntroduction($agent->getIntroduction());
            $row->setMessages($agent->getMessages() !== null ? Parser::encode($agent->getMessages()) : null);
            $row->setTools($agent->getTools() !== null ? Parser::encode($agent->getTools()) : null);
            $row->setOutgoing($agent->getOutgoing());
            $row->setAction($agent->getAction());
            $row->setInsertDate(LocalDateTime::now());
            $row->setMetadata($agent->getMetadata() !== null ? Parser::encode($agent->getMetadata()) : null);
            $this->agentTable->create($row);

            $agentId = $this->agentTable->getLastInsertId();
            $agent->setId($agentId);

            $this->agentTable->commit();
        } catch (\Throwable $e) {
            $this->agentTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($agent, $context));

        return $agentId;
    }

    public function update(string $agentId, AgentUpdate $agent, UserContext $context): int
    {
        $existing = $this->agentTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $agentId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find agent');
        }

        if ($existing->getStatus() == Table\Agent::STATUS_DELETED) {
            throw new StatusCode\GoneException('Agent was deleted');
        }

        $this->validator->assert($agent, $context->getCategoryId(), $context->getTenantId(), $existing);

        $existing->setName($agent->getName() ?? $existing->getName());
        $existing->setDescription($agent->getDescription() ?? $existing->getDescription());
        $existing->setIntroduction($agent->getIntroduction() ?? $existing->getIntroduction());
        $existing->setMessages($agent->getMessages() !== null ? Parser::encode($agent->getMessages()) : $existing->getMessages());
        $existing->setTools($agent->getTools() !== null ? Parser::encode($agent->getTools()) : $existing->getTools());
        $existing->setOutgoing($agent->getOutgoing() ?? $existing->getOutgoing());
        $existing->setAction($agent->getAction() ?? $existing->getAction());
        $existing->setMetadata($agent->getMetadata() !== null ? Parser::encode($agent->getMetadata()) : $existing->getMetadata());
        $this->agentTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($agent, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $agentId, UserContext $context): int
    {
        $existing = $this->agentTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $agentId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find agent');
        }

        if ($existing->getStatus() == Table\Agent::STATUS_DELETED) {
            throw new StatusCode\GoneException('Agent was deleted');
        }

        $existing->setStatus(Table\Agent::STATUS_DELETED);
        $this->agentTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}

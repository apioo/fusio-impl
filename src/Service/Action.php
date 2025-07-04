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

use Fusio\Engine\Action\LifecycleInterface;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\Exception\FactoryResolveException;
use Fusio\Engine\Factory;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Engine\Parameters;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Action\CreatedEvent;
use Fusio\Impl\Event\Action\DeletedEvent;
use Fusio\Impl\Event\Action\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\ActionCreate;
use Fusio\Model\Backend\ActionUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Action
{
    public function __construct(
        private Table\Action $actionTable,
        private Factory\ActionInterface $actionFactory,
        private Action\Validator $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(ActionCreate $action, UserContext $context): int
    {
        $this->validator->assert($action, $context->getTenantId());

        $name = $action->getName() ?? throw new StatusCode\BadRequestException('Provided no action name');
        $class = $action->getClass() ?? throw new StatusCode\BadRequestException('Provided no action class');

        // check source
        $config     = $action->getConfig()?->getAll() ?? [];
        $parameters = new Parameters($config);
        $handler    = $this->newAction($class);

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onCreate($name, $parameters);
        }

        // create action
        try {
            $this->actionTable->beginTransaction();

            $row = new Table\Generated\ActionRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setStatus(Table\Action::STATUS_ACTIVE);
            $row->setName($name);
            $row->setClass(ClassName::serialize($class));
            $row->setAsync($action->getAsync() ?? false);
            $row->setConfig(self::serializeConfig($config));
            $row->setMetadata($action->getMetadata() !== null ? Parser::encode($action->getMetadata()) : null);
            $row->setDate(LocalDateTime::now());
            $this->actionTable->create($row);

            $actionId = $this->actionTable->getLastInsertId();
            $action->setId($actionId);

            $this->actionTable->commit();
        } catch (\Throwable $e) {
            $this->actionTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($action, $context));

        return $actionId;
    }

    public function update(string $actionId, ActionUpdate $action, UserContext $context): int
    {
        $existing = $this->actionTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $actionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find action');
        }

        if ($existing->getStatus() == Table\Action::STATUS_DELETED) {
            throw new StatusCode\GoneException('Action was deleted');
        }

        $this->validator->assert($action, $context->getTenantId(), $existing);

        $name = $action->getName() ?? $existing->getName();
        $class = $action->getClass() ?? $existing->getClass();

        // check source
        $config     = $action->getConfig()?->getAll() ?? self::unserializeConfig($existing->getConfig());
        $parameters = new Parameters($config ?? []);
        $handler    = $this->newAction($class);

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onUpdate($name, $parameters);
        }

        // update action
        $existing->setName($name);
        $existing->setClass(ClassName::serialize($class));
        $existing->setAsync($action->getAsync() ?? $existing->getAsync());
        $existing->setConfig(self::serializeConfig($config));
        $existing->setMetadata($action->getMetadata() !== null ? Parser::encode($action->getMetadata()) : $existing->getMetadata());
        $existing->setDate(LocalDateTime::now());
        $this->actionTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($action, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $actionId, UserContext $context): int
    {
        $existing = $this->actionTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $actionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find action');
        }

        if ($existing->getStatus() == Table\Action::STATUS_DELETED) {
            throw new StatusCode\GoneException('Action was deleted');
        }

        $config     = self::unserializeConfig($existing->getConfig());
        $parameters = new Parameters($config ?: []);
        $handler    = $this->newAction($existing->getClass());

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onDelete($existing->getName(), $parameters);
        }

        $existing->setStatus(Table\Action::STATUS_DELETED);
        $this->actionTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    /**
     * Checks whether the provided class is resolvable and returns an action instance
     */
    private function newAction(string $class): ActionInterface
    {
        try {
            return $this->actionFactory->factory($class);
        } catch (FactoryResolveException $e) {
            throw new StatusCode\BadRequestException($e->getMessage());
        }
    }

    public static function serializeConfig(?array $config = null): ?string
    {
        if (empty($config)) {
            return null;
        }

        return Parser::encode($config);
    }

    public static function unserializeConfig(?string $data): ?array
    {
        if (empty($data)) {
            return null;
        }

        return Parser::decode($data, true);
    }
}

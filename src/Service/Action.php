<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Engine\Factory\Resolver;
use Fusio\Engine\Parameters;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Action\CreatedEvent;
use Fusio\Impl\Event\Action\DeletedEvent;
use Fusio\Impl\Event\Action\UpdatedEvent;
use Fusio\Impl\Factory\EngineDetector;
use Fusio\Impl\Table;
use Fusio\Model\Backend\ActionCreate;
use Fusio\Model\Backend\ActionUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Action
{
    private Table\Action $actionTable;
    private Factory\ActionInterface $actionFactory;
    private Action\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Action $actionTable, Factory\ActionInterface $actionFactory, Action\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->actionTable = $actionTable;
        $this->actionFactory = $actionFactory;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, ActionCreate $action, UserContext $context): int
    {
        $this->validator->assert($action);

        $name = $action->getName();
        $class = $action->getClass();

        $engine = $action->getEngine();
        if (empty($engine)) {
            $engine = EngineDetector::getEngine($class);
        }

        // check source
        $config     = $action->getConfig() ? $action->getConfig()->getAll() : [];
        $parameters = new Parameters($config);
        $handler    = $this->newAction($class, $engine);

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onCreate($name, $parameters);
        }

        // create action
        try {
            $this->actionTable->beginTransaction();

            $row = new Table\Generated\ActionRow();
            $row->setCategoryId($categoryId);
            $row->setStatus(Table\Action::STATUS_ACTIVE);
            $row->setName($name);
            $row->setClass($class);
            $row->setAsync($action->getAsync() ?? false);
            $row->setEngine($engine);
            $row->setConfig(self::serializeConfig($config));
            $row->setMetadata($action->getMetadata() !== null ? json_encode($action->getMetadata()) : null);
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
        $existing = $this->actionTable->findOneByIdentifier($actionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find action');
        }

        if ($existing->getStatus() == Table\Action::STATUS_DELETED) {
            throw new StatusCode\GoneException('Action was deleted');
        }

        $this->validator->assert($action, $existing);

        $name = $action->getName() ?? $existing->getName();
        $class = $action->getClass() ?? $existing->getClass();
        $engine = $action->getEngine() ?? $existing->getEngine();

        // check source
        $config     = $action->getConfig()?->getAll() ?? self::unserializeConfig($existing->getConfig());
        $parameters = new Parameters($config);
        $handler    = $this->newAction($class, $engine);

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onUpdate($name, $parameters);
        }

        // update action
        $existing->setName($name);
        $existing->setClass($class);
        $existing->setAsync($action->getAsync() ?? $existing->getAsync());
        $existing->setEngine($engine);
        $existing->setConfig(self::serializeConfig($config));
        $existing->setMetadata($action->getMetadata() !== null ? json_encode($action->getMetadata()) : $existing->getMetadata());
        $existing->setDate(LocalDateTime::now());
        $this->actionTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($action, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $actionId, UserContext $context): int
    {
        $existing = $this->actionTable->findOneByIdentifier($actionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find action');
        }

        if ($existing->getStatus() == Table\Action::STATUS_DELETED) {
            throw new StatusCode\GoneException('Action was deleted');
        }

        $config     = self::unserializeConfig($existing->getConfig());
        $parameters = new Parameters($config ?: []);
        $handler    = $this->newAction($existing->getClass(), $existing->getEngine() ?? Resolver\PhpClass::class);

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
    private function newAction(string $class, string $engine): ActionInterface
    {
        if (!class_exists($engine)) {
            throw new StatusCode\BadRequestException('Could not resolve engine');
        }

        try {
            $action = $this->actionFactory->factory($class, $engine);
        } catch (FactoryResolveException $e) {
            throw new StatusCode\BadRequestException($e->getMessage());
        }

        return $action;
    }

    public static function serializeConfig(?array $config = null): ?string
    {
        if (empty($config)) {
            return null;
        }

        return \json_encode($config);
    }

    public static function unserializeConfig(?string $data): ?array
    {
        if (empty($data)) {
            return null;
        }

        return \json_decode($data, true);
    }
}

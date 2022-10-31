<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Service;

use Fusio\Adapter\Php\Action\PhpSandbox;
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
use Fusio\Model\Backend;
use Fusio\Model\Backend\ActionCreate;
use Fusio\Model\Backend\ActionUpdate;
use PSX\Dependency\Exception\AutowiredException;
use PSX\Dependency\Exception\NotFoundException;
use PSX\Framework\Config\Config as FrameworkConfig;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Action
{
    private Table\Action $actionTable;
    private Table\Route\Method $routeMethodTable;
    private Factory\ActionInterface $actionFactory;
    private FrameworkConfig $config;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Action $actionTable, Table\Route\Method $routeMethodTable, Factory\ActionInterface $actionFactory, FrameworkConfig $config, EventDispatcherInterface $eventDispatcher)
    {
        $this->actionTable      = $actionTable;
        $this->routeMethodTable = $routeMethodTable;
        $this->actionFactory    = $actionFactory;
        $this->config           = $config;
        $this->eventDispatcher  = $eventDispatcher;
    }

    public function create(int $categoryId, ActionCreate $action, UserContext $context): int
    {
        $this->assertSandboxAccess($action);

        $name = $action->getName();
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid action name');
        }

        // check whether action exists
        if ($this->exists($name)) {
            throw new StatusCode\BadRequestException('Action already exists');
        }

        $class = $action->getClass();
        if (empty($class)) {
            throw new StatusCode\BadRequestException('Action class not available');
        }

        $engine = $action->getEngine();
        if (empty($engine)) {
            $engine = EngineDetector::getEngine($class);
        }

        // check source
        $config     = $action->getConfig() ? $action->getConfig()?->getProperties() : [];
        $parameters = new Parameters($config ?? []);
        $handler    = $this->newAction($class, $engine);

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onCreate($name, $parameters);
        }

        // create action
        try {
            $this->actionTable->beginTransaction();

            $record = new Table\Generated\ActionRow([
                Table\Generated\ActionTable::COLUMN_CATEGORY_ID => $categoryId,
                Table\Generated\ActionTable::COLUMN_STATUS => Table\Action::STATUS_ACTIVE,
                Table\Generated\ActionTable::COLUMN_NAME => $action->getName(),
                Table\Generated\ActionTable::COLUMN_CLASS => $class,
                Table\Generated\ActionTable::COLUMN_ASYNC => $action->getAsync(),
                Table\Generated\ActionTable::COLUMN_ENGINE => $engine,
                Table\Generated\ActionTable::COLUMN_CONFIG => self::serializeConfig($config),
                Table\Generated\ActionTable::COLUMN_METADATA => $action->getMetadata() !== null ? json_encode($action->getMetadata()) : null,
                Table\Generated\ActionTable::COLUMN_DATE => new \DateTime(),
            ]);

            $this->actionTable->create($record);

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

    public function update(int $actionId, ActionUpdate $action, UserContext $context): int
    {
        $this->assertSandboxAccess($action);

        $existing = $this->actionTable->find($actionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find action');
        }

        if ($existing->getStatus() == Table\Action::STATUS_DELETED) {
            throw new StatusCode\GoneException('Action was deleted');
        }

        $name = $action->getName();
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid action name');
        }

        // in case the class is empty use the existing class
        $class = $action->getClass();
        if (empty($class)) {
            $class = $existing->getClass();
        }

        $engine = $action->getEngine();
        if (empty($engine)) {
            $engine = $existing->getEngine() ?? Resolver\PhpClass::class;
        }

        // check source
        $config     = $action->getConfig() ? $action->getConfig()?->getProperties() : [];
        $parameters = new Parameters($config ?? []);
        $handler    = $this->newAction($class, $engine);

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onUpdate($name, $parameters);
        }

        // update action
        $record = new Table\Generated\ActionRow([
            Table\Generated\ActionTable::COLUMN_ID => $existing->getId(),
            Table\Generated\ActionTable::COLUMN_NAME => $name,
            Table\Generated\ActionTable::COLUMN_CLASS => $class,
            Table\Generated\ActionTable::COLUMN_ASYNC => $action->getAsync(),
            Table\Generated\ActionTable::COLUMN_ENGINE => $engine,
            Table\Generated\ActionTable::COLUMN_CONFIG => self::serializeConfig($config),
            Table\Generated\ActionTable::COLUMN_METADATA => $action->getMetadata() !== null ? json_encode($action->getMetadata()) : null,
            Table\Generated\ActionTable::COLUMN_DATE => new \DateTime(),
        ]);

        $this->actionTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($action, $existing, $context));

        return $actionId;
    }

    public function delete(int $actionId, UserContext $context): int
    {
        $existing = $this->actionTable->find($actionId);
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

        $record = new Table\Generated\ActionRow([
            Table\Generated\ActionTable::COLUMN_ID => $existing->getId(),
            Table\Generated\ActionTable::COLUMN_STATUS => Table\Action::STATUS_DELETED,
        ]);

        $this->actionTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $actionId;
    }

    public function exists(string $name): int|false
    {
        $condition = new Condition();
        $condition->equals(Table\Generated\ActionTable::COLUMN_STATUS, Table\Action::STATUS_ACTIVE);
        $condition->equals(Table\Generated\ActionTable::COLUMN_NAME, $name);

        $action = $this->actionTable->findOneBy($condition);

        if ($action instanceof Table\Generated\ActionRow) {
            return $action->getId();
        } else {
            return false;
        }
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
        } catch (FactoryResolveException|NotFoundException|AutowiredException $e) {
            throw new StatusCode\BadRequestException($e->getMessage());
        }

        return $action;
    }

    private function assertSandboxAccess(Backend\Action $record): void
    {
        $class = ltrim((string) $record->getClass(), '\\');

        if (!$this->config->get('fusio_php_sandbox') && strcasecmp($class, PhpSandbox::class) == 0) {
            throw new StatusCode\BadRequestException('Usage of the PHP sandbox feature is disabled. To activate it set the key "fusio_php_sandbox" in the configuration.php file to "true"');
        }
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

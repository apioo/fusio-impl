<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\Action\LifecycleInterface;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\Exception\FactoryResolveException;
use Fusio\Engine\Factory;
use Fusio\Engine\Parameters;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Action_Create;
use Fusio\Impl\Backend\Model\Action_Update;
use Fusio\Impl\Event\Action\CreatedEvent;
use Fusio\Impl\Event\Action\DeletedEvent;
use Fusio\Impl\Event\Action\UpdatedEvent;
use Fusio\Impl\Event\ActionEvents;
use Fusio\Impl\Factory\EngineDetector;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Action
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Action
{
    /**
     * @var \Fusio\Impl\Table\Action
     */
    protected $actionTable;

    /**
     * @var \Fusio\Impl\Table\Route\Method
     */
    protected $routesMethodTable;

    /**
     * @var \Fusio\Engine\Factory\ActionInterface
     */
    protected $actionFactory;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Action $actionTable
     * @param \Fusio\Impl\Table\Route\Method $routesMethodTable
     * @param \Fusio\Engine\Factory\ActionInterface $actionFactory
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Action $actionTable, Table\Route\Method $routesMethodTable, Factory\ActionInterface $actionFactory, EventDispatcherInterface $eventDispatcher)
    {
        $this->actionTable       = $actionTable;
        $this->routesMethodTable = $routesMethodTable;
        $this->actionFactory     = $actionFactory;
        $this->eventDispatcher   = $eventDispatcher;
    }

    public function create(Action_Create $action, UserContext $context)
    {
        // check whether action exists
        if ($this->exists($action->getName())) {
            throw new StatusCode\BadRequestException('Action already exists');
        }

        $engine = $action->getEngine();
        $class  = $action->getClass();
        if (empty($engine)) {
            $engine = EngineDetector::getEngine($class);
        }

        // check source
        $config     = $action->getConfig() ? $action->getConfig()->getProperties() : [];
        $parameters = new Parameters($config);
        $handler    = $this->newAction($class, $engine);

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onCreate($action->getName(), $parameters);
        }

        // create action
        $record = [
            'status' => Table\Action::STATUS_ACTIVE,
            'name'   => $action->getName(),
            'class'  => $class,
            'engine' => $engine,
            'config' => self::serializeConfig($config),
            'date'   => new \DateTime(),
        ];

        $this->actionTable->create($record);

        $actionId = $this->actionTable->getLastInsertId();
        $action->setId($actionId);

        $this->eventDispatcher->dispatch(new CreatedEvent($action, $context));

        return $actionId;
    }

    public function update(int $actionId, Action_Update $action, UserContext $context)
    {
        $existing = $this->actionTable->get($actionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find action');
        }

        if ($existing['status'] == Table\Action::STATUS_DELETED) {
            throw new StatusCode\GoneException('Action was deleted');
        }

        // in case the class is empty use the existing class
        $class = $action->getClass();
        if (empty($class)) {
            $class = $existing['class'];
        }

        $engine = $action->getEngine();
        if (empty($engine)) {
            $engine = $existing['engine'];
        }

        // check source
        $config     = $action->getConfig() ? $action->getConfig()->getProperties() : [];
        $parameters = new Parameters($config);
        $handler    = $this->newAction($class, $engine);

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onUpdate($action->getName(), $parameters);
        }

        // update action
        $record = [
            'id'     => $existing['id'],
            'name'   => $action->getName(),
            'class'  => $class,
            'engine' => $engine,
            'config' => self::serializeConfig($config),
            'date'   => new \DateTime(),
        ];

        $this->actionTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($action, $existing, $context));
    }

    public function delete(int $actionId, UserContext $context)
    {
        $existing = $this->actionTable->get($actionId);

        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find action');
        }

        if ($existing['status'] == Table\Action::STATUS_DELETED) {
            throw new StatusCode\GoneException('Action was deleted');
        }

        $config     = self::unserializeConfig($existing['config']);
        $parameters = new Parameters($config ?: []);
        $handler    = $this->newAction($existing['class'], $existing['engine']);

        // call lifecycle
        if ($handler instanceof LifecycleInterface) {
            $handler->onDelete($existing['name'], $parameters);
        }

        $this->actionTable->update([
            'id'     => $existing['id'],
            'status' => Table\Action::STATUS_DELETED,
        ]);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));
    }

    public function exists(string $name)
    {
        $condition  = new Condition();
        $condition->equals('status', Table\Action::STATUS_ACTIVE);
        $condition->equals('name', $name);

        $action = $this->actionTable->getOneBy($condition);

        if (!empty($action)) {
            return $action['id'];
        } else {
            return false;
        }
    }

    /**
     * Checks whether the provided class is resolvable and returns an action
     * instance
     * 
     * @param string $class
     * @param string $engine
     * @return \Fusio\Engine\ActionInterface
     */
    private function newAction($class, $engine)
    {
        if (!class_exists($engine)) {
            throw new StatusCode\BadRequestException('Could not resolve engine');
        }

        try {
            $action = $this->actionFactory->factory($class, $engine);
        } catch (FactoryResolveException $e) {
            throw new StatusCode\BadRequestException($e->getMessage());
        }

        if (!$action instanceof ActionInterface) {
            throw new StatusCode\BadRequestException('Could not resolve action');
        }

        return $action;
    }

    /**
     * @param array|null $config
     * @return string|null
     */
    public static function serializeConfig(array $config = null)
    {
        if (empty($config)) {
            return null;
        }

        return \json_encode($config);
    }

    /**
     * @param string $data
     * @return array|null
     */
    public static function unserializeConfig($data)
    {
        if (empty($data)) {
            return null;
        }

        // BC check whether data is PHP serialized
        if (substr($data, 0, 2) === 'a:') {
            return \unserialize($data);
        }

        return \json_decode($data, true);
    }
}

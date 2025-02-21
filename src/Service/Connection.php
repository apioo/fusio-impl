<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

use Fusio\Engine\Connection\DeploymentInterface;
use Fusio\Engine\Connection\LifecycleInterface;
use Fusio\Engine\Connection\PingableInterface;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Factory;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Engine\Parameters;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Connection\CreatedEvent;
use Fusio\Impl\Event\Connection\DeletedEvent;
use Fusio\Impl\Event\Connection\UpdatedEvent;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Fusio\Model\Backend\ConnectionCreate;
use Fusio\Model\Backend\ConnectionUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Connection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Connection
{
    public function __construct(
        private Table\Connection $connectionTable,
        private Connection\Validator $validator,
        private Factory\Connection $connectionFactory,
        private FrameworkConfig $frameworkConfig,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(ConnectionCreate $connection, UserContext $context): int
    {
        $this->validator->assert($connection, $context->getTenantId());

        $name = $connection->getName();
        $class = $connection->getClass();

        $config     = $connection->getConfig()?->getAll() ?? [];
        $parameters = new Parameters($config);
        $factory    = $this->connectionFactory->factory($class);

        // call deployment
        if ($factory instanceof DeploymentInterface) {
            $factory->onUp($name, $parameters);
        }

        $conn = $factory->getConnection($parameters);

        // test connection
        $this->testConnection($factory, $conn);

        // call lifecycle
        if ($factory instanceof LifecycleInterface) {
            $factory->onCreate($name, $parameters, $conn);
        }

        // create connection
        try {
            $this->connectionTable->beginTransaction();

            $row = new Table\Generated\ConnectionRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setStatus(Table\Connection::STATUS_ACTIVE);
            $row->setName($name);
            $row->setClass(ClassName::serialize($class));
            $row->setConfig(Connection\Encrypter::encrypt($parameters->toArray(), $this->frameworkConfig->getProjectKey()));
            $row->setMetadata($connection->getMetadata() !== null ? Parser::encode($connection->getMetadata()) : null);
            $this->connectionTable->create($row);

            $connectionId = $this->connectionTable->getLastInsertId();
            $connection->setId($connectionId);

            $this->connectionTable->commit();
        } catch (\Throwable $e) {
            $this->connectionTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($connection, $context));

        return $connectionId;
    }

    public function update(string $connectionId, ConnectionUpdate $connection, UserContext $context): int
    {
        $existing = $this->connectionTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $connectionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find connection');
        }

        if ($existing->getStatus() == Table\Connection::STATUS_DELETED) {
            throw new StatusCode\GoneException('Connection was deleted');
        }

        $this->validator->assert($connection, $context->getTenantId(), $existing);

        $class = $connection->getClass();

        $config     = $connection->getConfig()?->getAll() ?? Connection\Encrypter::decrypt($existing->getConfig(), $this->frameworkConfig->getProjectKey());
        $parameters = new Parameters($config);
        $factory    = $this->connectionFactory->factory($class);

        $conn = $factory->getConnection($parameters);

        // test connection
        $this->testConnection($factory, $conn);

        // call lifecycle
        if ($factory instanceof LifecycleInterface) {
            $factory->onUpdate($existing->getName(), $parameters, $conn);
        }

        // update connection
        $existing->setConfig(Connection\Encrypter::encrypt($parameters->toArray(), $this->frameworkConfig->getProjectKey()));
        $existing->setMetadata($connection->getMetadata() !== null ? Parser::encode($connection->getMetadata()) : $existing->getMetadata());
        $this->connectionTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($connection, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $connectionId, UserContext $context): int
    {
        $existing = $this->connectionTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $connectionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find connection');
        }

        if ($existing->getStatus() == Table\Connection::STATUS_DELETED) {
            throw new StatusCode\GoneException('Connection was deleted');
        }

        $config = Connection\Encrypter::decrypt($existing->getConfig(), $this->frameworkConfig->getProjectKey());

        $parameters = new Parameters($config ?: []);
        $factory    = $this->connectionFactory->factory($existing->getClass());

        // call deployment
        if ($factory instanceof DeploymentInterface) {
            $factory->onDown($existing->getName(), $parameters);
        }

        $conn = $factory->getConnection($parameters);

        // call lifecycle
        if ($factory instanceof LifecycleInterface) {
            $factory->onDelete($existing->getName(), $parameters, $conn);
        }

        $existing->setStatus(Table\Connection::STATUS_DELETED);
        $this->connectionTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    protected function testConnection(ConnectionInterface $factory, object $connection): void
    {
        if ($factory instanceof PingableInterface) {
            try {
                $ping = $factory->ping($connection);
            } catch (\Throwable $e) {
                throw new StatusCode\BadRequestException($e->getMessage());
            }

            if (!$ping) {
                throw new StatusCode\BadRequestException('Could not connect to remote service');
            }
        }
    }
}

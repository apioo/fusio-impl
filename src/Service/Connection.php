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

use Fusio\Engine\Connection\DeploymentInterface;
use Fusio\Engine\Connection\IntrospectableInterface;
use Fusio\Engine\Connection\Introspection\IntrospectorInterface;
use Fusio\Engine\Connection\LifecycleInterface;
use Fusio\Engine\Connection\PingableInterface;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Factory;
use Fusio\Engine\Parameters;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Connection\CreatedEvent;
use Fusio\Impl\Event\Connection\DeletedEvent;
use Fusio\Impl\Event\Connection\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\ConnectionCreate;
use Fusio\Model\Backend\ConnectionUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Connection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Connection
{
    private Table\Connection $connectionTable;
    private Connection\Validator $validator;
    private Factory\Connection $connectionFactory;
    private string $secretKey;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Connection $connectionTable, Connection\Validator $validator, Factory\Connection $connectionFactory, ConfigInterface $config, EventDispatcherInterface $eventDispatcher)
    {
        $this->connectionTable = $connectionTable;
        $this->validator = $validator;
        $this->connectionFactory = $connectionFactory;
        $this->secretKey = $config->get('fusio_project_key');
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(ConnectionCreate $connection, UserContext $context): int
    {
        $this->validator->assert($connection);

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
            $row->setStatus(Table\Connection::STATUS_ACTIVE);
            $row->setName($name);
            $row->setClass($class);
            $row->setConfig(Connection\Encrypter::encrypt($parameters->toArray(), $this->secretKey));
            $row->setMetadata($connection->getMetadata() !== null ? json_encode($connection->getMetadata()) : null);
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
        $existing = $this->connectionTable->findOneByIdentifier($connectionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find connection');
        }

        if ($existing->getStatus() == Table\Connection::STATUS_DELETED) {
            throw new StatusCode\GoneException('Connection was deleted');
        }

        $this->validator->assert($connection, $existing);

        $class = $connection->getClass();

        $config     = $connection->getConfig()?->getAll() ?? Connection\Encrypter::decrypt($existing->getConfig(), $this->secretKey);
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
        $existing->setConfig(Connection\Encrypter::encrypt($parameters->toArray(), $this->secretKey));
        $existing->setMetadata($connection->getMetadata() !== null ? json_encode($connection->getMetadata()) : $existing->getMetadata());
        $this->connectionTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($connection, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $connectionId, UserContext $context): int
    {
        $existing = $this->connectionTable->findOneByIdentifier($connectionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find connection');
        }

        if ($existing->getStatus() == Table\Connection::STATUS_DELETED) {
            throw new StatusCode\GoneException('Connection was deleted');
        }

        $config = Connection\Encrypter::decrypt($existing->getConfig(), $this->secretKey);

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

    public function getIntrospection(int $connectionId): IntrospectorInterface
    {
        $existing = $this->connectionTable->find($connectionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find connection');
        }

        $factory = $this->connectionFactory->factory($existing->getClass());
        if (!$factory instanceof IntrospectableInterface) {
            throw new StatusCode\InternalServerErrorException('Provided connection is not introspectable');
        }

        $config = Connection\Encrypter::decrypt($existing->getConfig(), $this->secretKey);
        $parameters = new Parameters($config ?: []);

        $connection = $factory->getConnection($parameters);

        return $factory->getIntrospector($connection);
    }

    protected function testConnection(ConnectionInterface $factory, object $connection)
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

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
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Connection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Connection
{
    private Table\Connection $connectionTable;
    private Factory\Connection $connectionFactory;
    private string $secretKey;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Connection $connectionTable, Factory\Connection $connectionFactory, $secretKey, EventDispatcherInterface $eventDispatcher)
    {
        $this->connectionTable   = $connectionTable;
        $this->connectionFactory = $connectionFactory;
        $this->secretKey         = $secretKey;
        $this->eventDispatcher   = $eventDispatcher;
    }

    public function create(ConnectionCreate $connection, UserContext $context): int
    {
        $name = $connection->getName();
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Name not provided');
        }

        // check whether connection exists
        if ($this->exists($name)) {
            throw new StatusCode\BadRequestException('Connection already exists');
        }

        $config     = $connection->getConfig();
        $parameters = new Parameters($config !== null ? $config->getProperties() : []);
        $factory    = $this->connectionFactory->factory($connection->getClass() ?? '');

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

            $record = new Table\Generated\ConnectionRow([
                Table\Generated\ConnectionTable::COLUMN_STATUS => Table\Connection::STATUS_ACTIVE,
                Table\Generated\ConnectionTable::COLUMN_NAME => $connection->getName(),
                Table\Generated\ConnectionTable::COLUMN_CLASS => $connection->getClass(),
                Table\Generated\ConnectionTable::COLUMN_CONFIG => Connection\Encrypter::encrypt($parameters->toArray(), $this->secretKey),
                Table\Generated\ConnectionTable::COLUMN_METADATA => $connection->getMetadata() !== null ? json_encode($connection->getMetadata()) : null,
            ]);

            $this->connectionTable->create($record);

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

    public function update(int $connectionId, ConnectionUpdate $connection, UserContext $context): int
    {
        $existing = $this->connectionTable->find($connectionId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find connection');
        }

        if ($existing->getStatus() == Table\Connection::STATUS_DELETED) {
            throw new StatusCode\GoneException('Connection was deleted');
        }

        $config     = $connection->getConfig();
        $parameters = new Parameters($config !== null ? $config->getProperties() : []);
        $factory    = $this->connectionFactory->factory($connection->getClass() ?? '');

        $conn = $factory->getConnection($parameters);

        // test connection
        $this->testConnection($factory, $conn);

        // call lifecycle
        if ($factory instanceof LifecycleInterface) {
            $factory->onUpdate($existing->getName(), $parameters, $conn);
        }

        // update connection
        $record = new Table\Generated\ConnectionRow([
            Table\Generated\ConnectionTable::COLUMN_ID => $existing->getId(),
            Table\Generated\ConnectionTable::COLUMN_CONFIG => Connection\Encrypter::encrypt($parameters->toArray(), $this->secretKey),
            Table\Generated\ConnectionTable::COLUMN_METADATA => $connection->getMetadata() !== null ? json_encode($connection->getMetadata()) : null,
        ]);

        $this->connectionTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($connection, $existing, $context));

        return $connectionId;
    }

    public function delete(int $connectionId, UserContext $context): int
    {
        $existing = $this->connectionTable->find($connectionId);
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

        $record = new Table\Generated\ConnectionRow([
            Table\Generated\ConnectionTable::COLUMN_ID => $existing->getId(),
            Table\Generated\ConnectionTable::COLUMN_STATUS => Table\Connection::STATUS_DELETED,
        ]);

        $this->connectionTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $connectionId;
    }

    public function exists(string $name): int|false
    {
        $condition  = new Condition();
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_STATUS, Table\Connection::STATUS_ACTIVE);
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_NAME, $name);

        $connection = $this->connectionTable->findOneBy($condition);

        if ($connection instanceof Table\Generated\ConnectionRow) {
            return $connection->getId();
        } else {
            return false;
        }
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

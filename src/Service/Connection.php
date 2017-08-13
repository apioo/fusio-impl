<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\Connection\PingableInterface;
use Fusio\Engine\Factory;
use Fusio\Engine\Parameters;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Connection\CreatedEvent;
use Fusio\Impl\Event\Connection\DeletedEvent;
use Fusio\Impl\Event\Connection\UpdatedEvent;
use Fusio\Impl\Event\ConnectionEvents;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use PSX\OpenSsl\OpenSsl;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Connection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Connection
{
    const CIPHER_METHOD = 'AES-128-CBC';

    /**
     * @var \Fusio\Impl\Table\Connection
     */
    protected $connectionTable;

    /**
     * @var \Fusio\Engine\Factory\Connection
     */
    protected $connectionFactory;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Connection $connectionTable
     * @param \Fusio\Engine\Factory\Connection $connectionFactory
     * @param string $secretKey
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Connection $connectionTable, Factory\Connection $connectionFactory, $secretKey, EventDispatcherInterface $eventDispatcher)
    {
        $this->connectionTable   = $connectionTable;
        $this->connectionFactory = $connectionFactory;
        $this->secretKey         = $secretKey;
        $this->eventDispatcher   = $eventDispatcher;
    }

    public function create($name, $class, $config, UserContext $context)
    {
        // check whether connection exists
        $condition  = new Condition();
        $condition->equals('status', Table\Connection::STATUS_ACTIVE);
        $condition->equals('name', $name);

        $connection = $this->connectionTable->getOneBy($condition);

        if (!empty($connection)) {
            throw new StatusCode\BadRequestException('Connection already exists');
        }

        $this->testConnection($class, $config);

        // create connection
        $record = [
            'status' => Table\Connection::STATUS_ACTIVE,
            'name'   => $name,
            'class'  => $class,
            'config' => self::encryptConfig($config, $this->secretKey),
        ];

        $this->connectionTable->create($record);

        $connectionId = $this->connectionTable->getLastInsertId();

        $this->eventDispatcher->dispatch(ConnectionEvents::CREATE, new CreatedEvent($connectionId, $record, $context));
    }

    public function update($connectionId, $name, $class, $config, UserContext $context)
    {
        $connection = $this->connectionTable->get($connectionId);

        if (empty($connection)) {
            throw new StatusCode\NotFoundException('Could not find connection');
        }

        if ($connection['status'] == Table\Connection::STATUS_DELETED) {
            throw new StatusCode\GoneException('Connection was deleted');
        }

        $this->testConnection($class, $config);

        $record = [
            'id'     => $connection->id,
            'name'   => $name,
            'class'  => $class,
            'config' => self::encryptConfig($config, $this->secretKey),
        ];

        $this->connectionTable->update($record);

        $this->eventDispatcher->dispatch(ConnectionEvents::UPDATE, new UpdatedEvent($connectionId, $record, $connection, $context));
    }

    public function delete($connectionId, UserContext $context)
    {
        $connection = $this->connectionTable->get($connectionId);

        if (empty($connection)) {
            throw new StatusCode\NotFoundException('Could not find connection');
        }

        if ($connection['status'] == Table\Connection::STATUS_DELETED) {
            throw new StatusCode\GoneException('Connection was deleted');
        }

        $record = [
            'id'     => $connection->id,
            'status' => Table\Connection::STATUS_DELETED,
        ];

        $this->connectionTable->update($record);

        $this->eventDispatcher->dispatch(ConnectionEvents::DELETE, new DeletedEvent($connectionId, $connection, $context));
    }

    protected function testConnection($class, array $config)
    {
        $factory    = $this->connectionFactory->factory($class);
        $connection = $factory->getConnection(new Parameters($config));

        if (!is_object($connection)) {
            throw new StatusCode\BadRequestException('Invalid connection');
        }

        if ($factory instanceof PingableInterface) {
            try {
                $ping = $factory->ping($connection);
            } catch (\Exception $e) {
                throw new StatusCode\BadRequestException($e->getMessage());
            }

            if (!$ping) {
                throw new StatusCode\BadRequestException('Could not connect to remote service');
            }
        }
    }

    public static function encryptConfig($config, $secretKey)
    {
        if (empty($config)) {
            return null;
        }

        $iv   = OpenSsl::randomPseudoBytes(openssl_cipher_iv_length(self::CIPHER_METHOD));
        $data = Parser::encode($config);
        $data = OpenSsl::encrypt($data, self::CIPHER_METHOD, $secretKey, OPENSSL_RAW_DATA, $iv);

        return base64_encode($iv) . '.' . base64_encode($data);
    }

    public static function decryptConfig($data, $secretKey)
    {
        if (empty($data)) {
            return [];
        }

        if (is_resource($data)) {
            $data = stream_get_contents($data, -1, 0);
        }

        $parts = explode('.', $data, 2);
        if (count($parts) == 2) {
            list($iv, $data) = $parts;

            $config = OpenSsl::decrypt(base64_decode($data), self::CIPHER_METHOD, $secretKey, OPENSSL_RAW_DATA, base64_decode($iv));
            $config = Parser::decode($config, true);

            return $config;
        } else {
            return [];
        }
    }
}

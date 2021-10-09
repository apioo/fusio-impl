<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\EventListener;

use Fusio\Impl\Event;
use Fusio\Impl\Worker\Action\WorkerJava;
use Fusio\Impl\Worker\Action\WorkerJavascript;
use Fusio\Impl\Worker\Action\WorkerPHP;
use Fusio\Impl\Worker\Action\WorkerPython;
use Fusio\Impl\Worker\ClientFactory;
use Fusio\Impl\Worker\Generated\Action;
use Fusio\Impl\Worker\Generated\Connection;
use Fusio\Impl\Worker\Generated\Message;
use Fusio\Model\Backend\Action_Config;
use Fusio\Model\Backend\Connection_Config;
use PSX\Framework\Config\Config;
use PSX\Http\Exception\InternalServerErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thrift\Exception\TException;

/**
 * WorkerListener
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class WorkerListener implements EventSubscriberInterface
{
    private const MAPPING = [
        WorkerJavascript::class => 'javascript',
        WorkerJava::class => 'java',
        WorkerPHP::class => 'php',
        WorkerPython::class => 'python',
    ];

    /**
     * @var Config
     */
    private $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function onActionCreate(Event\Action\CreatedEvent $event)
    {
        $this->notifyWorkerAction(
            $event->getAction()->getName(),
            $event->getAction()->getClass(),
            $event->getAction()->getConfig()
        );
    }

    public function onActionDelete(Event\Action\DeletedEvent $event)
    {

    }

    public function onActionUpdate(Event\Action\UpdatedEvent $event)
    {
        $this->notifyWorkerAction(
            $event->getAction()->getName(),
            $event->getAction()->getClass(),
            $event->getAction()->getConfig()
        );
    }

    public function onConnectionCreate(Event\Connection\CreatedEvent $event)
    {
        $this->notifyWorkerConnection(
            $event->getConnection()->getName(),
            $event->getConnection()->getClass(),
            $event->getConnection()->getConfig()
        );
    }

    public function onConnectionDelete(Event\Connection\DeletedEvent $event)
    {
    }

    public function onConnectionUpdate(Event\Connection\UpdatedEvent $event)
    {
        $this->notifyWorkerConnection(
            $event->getConnection()->getName(),
            $event->getConnection()->getClass(),
            $event->getConnection()->getConfig()
        );
    }

    private function notifyWorkerConnection(string $name, ?string $class, ?Connection_Config $config)
    {
        if (empty($class)) {
            return;
        }

        $worker = $this->config->get('fusio_worker');
        if (empty($worker) || !is_array($worker)) {
            return;
        }

        foreach ($worker as $type => $endpoint) {
            $connection = new Connection();
            $connection->name = $name;
            $connection->type = $this->convertClassToType($class);
            if ($config !== null) {
                $connection->config = $config->getProperties();
            }

            try {
                ClientFactory::getClient($endpoint, $type)->setConnection($connection);
            } catch (TException $e) {
                // in this case the worker is not reachable so we simply ignore this so that we can still save and use
                // the backend
                continue;
            }
        }
    }

    private function notifyWorkerAction(string $name, ?string $class, ?Action_Config $config)
    {
        if (empty($class)) {
            return;
        }

        $worker = $this->config->get('fusio_worker');
        if (empty($worker) || !is_array($worker)) {
            return;
        }

        $language = self::MAPPING[$class] ?? null;
        if (empty($language)) {
            return;
        }

        $endpoint = $worker[$language] ?? null;
        if (empty($endpoint)) {
            return;
        }

        if ($config === null) {
            return;
        }

        $action = new Action();
        $action->name = $name;
        $action->code = $config->getProperty('code');

        try {
            $message = ClientFactory::getClient($endpoint, $language)->setAction($action);
        } catch (TException $e) {
            // in this case the worker is not reachable so we simply ignore this so that we can still save and use
            // the backend
            return;
        }

        if ($message instanceof Message && $message->success === false) {
            throw new InternalServerErrorException('Worker returned an error: ' . $message->message);
        }
    }

    private function convertClassToType(?string $class): string
    {
        return str_replace('\\', '.', $class);
    }

    public static function getSubscribedEvents()
    {
        return [
            Event\Action\CreatedEvent::class        => 'onActionCreate',
            Event\Action\DeletedEvent::class        => 'onActionDelete',
            Event\Action\UpdatedEvent::class        => 'onActionUpdate',

            Event\Connection\CreatedEvent::class    => 'onConnectionCreate',
            Event\Connection\DeletedEvent::class    => 'onConnectionDelete',
            Event\Connection\UpdatedEvent::class    => 'onConnectionUpdate',
        ];
    }
}

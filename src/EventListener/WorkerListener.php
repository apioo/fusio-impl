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
use Fusio\Model\Backend\ActionConfig;
use Fusio\Model\Backend\ConnectionConfig;
use PSX\Framework\Config\Config;
use PSX\Http\Exception\InternalServerErrorException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thrift\Exception\TException;

/**
 * WorkerListener
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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

    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function onActionCreate(Event\Action\CreatedEvent $event): void
    {
        $this->notifyWorkerAction(
            $event->getAction()->getName(),
            $event->getAction()->getClass(),
            $event->getAction()->getConfig()
        );
    }

    public function onActionDelete(Event\Action\DeletedEvent $event): void
    {
    }

    public function onActionUpdate(Event\Action\UpdatedEvent $event): void
    {
        $this->notifyWorkerAction(
            $event->getAction()->getName(),
            $event->getAction()->getClass(),
            $event->getAction()->getConfig()
        );
    }

    public function onConnectionCreate(Event\Connection\CreatedEvent $event): void
    {
        $this->notifyWorkerConnection(
            $event->getConnection()->getName(),
            $event->getConnection()->getClass(),
            $event->getConnection()->getConfig()
        );
    }

    public function onConnectionDelete(Event\Connection\DeletedEvent $event): void
    {
    }

    public function onConnectionUpdate(Event\Connection\UpdatedEvent $event): void
    {
        $this->notifyWorkerConnection(
            $event->getConnection()->getName(),
            $event->getConnection()->getClass(),
            $event->getConnection()->getConfig()
        );
    }

    private function notifyWorkerConnection(?string $name, ?string $class, ?ConnectionConfig $config): void
    {
        if (empty($name) || empty($class)) {
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
                $connection->config = $config->getAll();
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

    private function notifyWorkerAction(?string $name, ?string $class, ?ActionConfig $config): void
    {
        if (empty($name) || empty($class)) {
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
        $action->code = $config->get('code');

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
        return str_replace('\\', '.', $class ?? '');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Event\Action\CreatedEvent::class => 'onActionCreate',
            Event\Action\DeletedEvent::class => 'onActionDelete',
            Event\Action\UpdatedEvent::class => 'onActionUpdate',

            Event\Connection\CreatedEvent::class => 'onConnectionCreate',
            Event\Connection\DeletedEvent::class => 'onConnectionDelete',
            Event\Connection\UpdatedEvent::class => 'onConnectionUpdate',
        ];
    }
}

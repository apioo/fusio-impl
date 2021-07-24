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

namespace Fusio\Impl\EventListener;

use Fusio\Impl\Base;
use Fusio\Impl\Event;
use Fusio\Impl\Worker\Action\JavascriptWorker;
use Fusio\Impl\Worker\Action\JavaWorker;
use Fusio\Impl\Worker\Action\PHPWorker;
use Fusio\Impl\Worker\Action\PythonWorker;
use Fusio\Model\Backend\Action_Config;
use Fusio\Model\Backend\Connection_Config;
use GuzzleHttp\Client;
use PSX\Framework\Config\Config;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * WorkerListener
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class WorkerListener implements EventSubscriberInterface
{
    private const MAPPING = [
        JavascriptWorker::class => 'javascript',
        JavaWorker::class => 'java',
        PHPWorker::class => 'php',
        PythonWorker::class => 'python',
    ];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Client
     */
    private $httpClient;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->httpClient = new Client();
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

        foreach ($worker as $endpoint) {
            $this->httpClient->post($endpoint . '/connection', [
                'headers' => [
                    'User-Agent' => Base::getUserAgent(),
                ],
                'json' => [
                    'name' => $name,
                    'type' => $this->convertClassToType($class),
                    'config' => $config,
                ]
            ]);
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

        $this->httpClient->post($endpoint . '/action', [
            'headers' => [
                'User-Agent' => Base::getUserAgent(),
            ],
            'json' => [
                'name' => $name,
                'code' => $config->getProperty('code'),
            ]
        ]);
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

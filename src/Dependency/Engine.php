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

namespace Fusio\Impl\Dependency;

use Fusio\Engine\Action;
use Fusio\Engine\Cache;
use Fusio\Engine\CacheInterface;
use Fusio\Engine\Connector;
use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\DispatcherInterface;
use Fusio\Engine\Factory;
use Fusio\Engine\Form;
use Fusio\Engine\Logger;
use Fusio\Engine\LoggerInterface;
use Fusio\Engine\Processor;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\Repository;
use Fusio\Engine\Response;
use Fusio\Engine\Serverless;
use Fusio\Impl\Factory\Resolver;
use Fusio\Impl\Provider\ActionProviderParser;
use Fusio\Impl\Provider\ConnectionProviderParser;
use Fusio\Impl\Provider\Push\Serverless\Executor;
use Fusio\Impl\Provider\Push\Serverless\Generator;
use Fusio\Impl\Repository as ImplRepository;
use Fusio\Impl\Service\Action\Queue;
use Fusio\Impl\Service\Event\Dispatcher;
use Fusio\Impl\Table;

/**
 * Engine
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
trait Engine
{
    public function getActionParser(): ActionProviderParser
    {
        return new ActionProviderParser(
            $this->get('action_factory'),
            $this->get('form_element_factory'),
            $this->get('provider_loader')
        );
    }

    public function getActionFactory(): Factory\ActionInterface
    {
        $factory = new Factory\Action($this, $this->get('container_type_resolver'));
        $factory->addResolver(new Factory\Resolver\PhpClass($this->get('container_autowire_resolver')));
        $factory->addResolver(new Resolver\HttpUrl());
        $factory->addResolver(new Resolver\PhpFile());
        $factory->addResolver(new Resolver\StaticFile());

        return $factory;
    }

    public function getActionQueue(): Action\QueueInterface
    {
        return new Queue\Producer(
            $this->get('connection')
        );
    }

    public function getEngineLogger(): LoggerInterface
    {
        $logger = new Logger('engine');
        $logger->pushHandler($this->newLoggerHandlerImpl());

        return $logger;
    }

    public function getEngineCache(): CacheInterface
    {
        return new Cache($this->newDoctrineCacheImpl('action'));
    }

    public function getActionRepository(): Repository\ActionInterface
    {
        return new ImplRepository\ActionDatabase($this->get('connection'));
    }

    public function getProcessor(): ProcessorInterface
    {
        return new Processor(
            $this->get('action_repository'),
            $this->get('action_factory'),
            $this->get('action_queue')
        );
    }

    public function getEngineDispatcher(): DispatcherInterface
    {
        return new Dispatcher(
            $this->get('table_manager')->getTable(Table\Event::class),
            $this->get('table_manager')->getTable(Table\Event\Trigger::class)
        );
    }

    public function getConnectionParser(): ConnectionProviderParser
    {
        return new ConnectionProviderParser(
            $this->get('connection_factory'),
            $this->get('form_element_factory'),
            $this->get('provider_loader')
        );
    }

    public function getConnectionFactory(): Factory\ConnectionInterface
    {
        return new Factory\Connection($this, $this->get('container_autowire_resolver'));
    }

    public function getConnectionRepository(): Repository\ConnectionInterface
    {
        return new ImplRepository\ConnectionDatabase($this->get('connection'), $this->get('config')->get('fusio_project_key'));
    }

    public function getConnector(): ConnectorInterface
    {
        return new Connector(
            $this->get('connection_repository'),
            $this->get('connection_factory')
        );
    }

    public function getAppRepository(): Repository\AppInterface
    {
        return new ImplRepository\AppDatabase($this->get('connection'));
    }

    public function getUserRepository(): Repository\UserInterface
    {
        return new ImplRepository\UserDatabase($this->get('connection'));
    }

    public function getFormElementFactory(): Form\ElementFactoryInterface
    {
        return new Form\ElementFactory(
            $this->get('action_repository'),
            $this->get('connection_repository')
        );
    }

    public function getEngineResponse(): Response\FactoryInterface
    {
        return new Response\Factory();
    }

    public function getServerlessExecutor(): Serverless\ExecutorInterface
    {
        return new Executor(
            $this,
            $this->get('dispatch')
        );
    }

    public function getServerlessGenerator(): Serverless\GeneratorInterface
    {
        return new Generator(
            $this->get('connection')
        );
    }
}

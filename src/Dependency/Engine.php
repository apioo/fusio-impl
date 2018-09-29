<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Connector;
use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\DispatcherInterface;
use Fusio\Engine\Factory;
use Fusio\Engine\Form;
use Fusio\Engine\Processor;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\Response;
use Fusio\Impl\Factory\Resolver;
use Fusio\Impl\Provider\ProviderConfig;
use Fusio\Impl\Provider\ProviderParser;
use Fusio\Impl\Repository as ImplRepository;
use Fusio\Impl\Schema as ImplSchema;
use Fusio\Impl\Service\Event\Dispatcher;
use Fusio\Impl\Table;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use PSX\Cache\SimpleCache;

/**
 * Engine
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
trait Engine
{
    /**
     * @return \Fusio\Engine\Parser\ParserInterface
     */
    public function getActionParser()
    {
        return new ProviderParser(
            $this->get('action_factory'),
            $this->get('form_element_factory'),
            $this->get('provider_config'),
            ProviderConfig::TYPE_ACTION,
            ActionInterface::class
        );
    }

    /**
     * @return \Fusio\Engine\Factory\ActionInterface
     */
    public function getActionFactory()
    {
        $services = [
            ConnectorInterface::class => 'connector',
            ProcessorInterface::class => 'processor',
            DispatcherInterface::class => 'engine_dispatcher',
            Response\FactoryInterface::class => 'engine_response',
            LoggerInterface::class => 'engine_logger',
            CacheInterface::class => 'engine_cache',
        ];

        $factory = new Factory\Action($this, $services);
        $factory->addResolver(new Resolver\HttpUrl());
        $factory->addResolver(new Resolver\JavascriptFile());
        $factory->addResolver(new Resolver\PhpFile());
        $factory->addResolver(new Resolver\StaticFile());

        return $factory;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getEngineLogger()
    {
        $logger = new Logger('action');
        $logger->pushHandler(new NullHandler());

        return $logger;
    }

    /**
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function getEngineCache()
    {
        return new SimpleCache($this->newDoctrineCacheImpl('action'));
    }

    /**
     * @return \Fusio\Engine\Repository\ActionInterface
     */
    public function getActionRepository()
    {
        return new ImplRepository\ActionDatabase($this->get('connection'));
    }

    /**
     * @return \Fusio\Engine\ProcessorInterface
     */
    public function getProcessor()
    {
        return new Processor(
            $this->get('action_repository'),
            $this->get('action_factory')
        );
    }

    /**
     * @return \Fusio\Engine\DispatcherInterface
     */
    public function getEngineDispatcher()
    {
        return new Dispatcher(
            $this->get('table_manager')->getTable(Table\Event::class),
            $this->get('table_manager')->getTable(Table\Event\Trigger::class)
        );
    }

    /**
     * @return \Fusio\Engine\Parser\ParserInterface
     */
    public function getConnectionParser()
    {
        return new ProviderParser(
            $this->get('connection_factory'),
            $this->get('form_element_factory'),
            $this->get('provider_config'),
            ProviderConfig::TYPE_CONNECTION,
            ConnectionInterface::class
        );
    }

    /**
     * @return \Fusio\Engine\Factory\ConnectionInterface
     */
    public function getConnectionFactory()
    {
        return new Factory\Connection($this);
    }

    /**
     * @return \Fusio\Engine\Repository\ConnectionInterface
     */
    public function getConnectionRepository()
    {
        return new ImplRepository\ConnectionDatabase($this->get('connection'), $this->get('config')->get('fusio_project_key'));
    }

    /**
     * @return \Fusio\Engine\ConnectorInterface
     */
    public function getConnector()
    {
        return new Connector(
            $this->get('connection_repository'),
            $this->get('connection_factory')
        );
    }

    /**
     * @return \Fusio\Engine\Schema\ParserInterface
     */
    public function getSchemaParser()
    {
        return new ImplSchema\Parser($this->get('connection'));
    }

    /**
     * @return \Fusio\Engine\Schema\LoaderInterface
     */
    public function getSchemaLoader()
    {
        return new ImplSchema\Loader($this->get('connection'));
    }

    /**
     * @return \Fusio\Engine\Repository\AppInterface
     */
    public function getAppRepository()
    {
        return new ImplRepository\AppDatabase($this->get('connection'));
    }

    /**
     * @return \Fusio\Engine\Repository\UserInterface
     */
    public function getUserRepository()
    {
        return new ImplRepository\UserDatabase($this->get('connection'));
    }

    /**
     * @return \Fusio\Engine\Form\ElementFactoryInterface
     */
    public function getFormElementFactory()
    {
        return new Form\ElementFactory(
            $this->get('action_repository'),
            $this->get('connection_repository')
        );
    }

    /**
     * @return \Fusio\Engine\Response\FactoryInterface
     */
    public function getEngineResponse()
    {
        return new Response\Factory();
    }
}

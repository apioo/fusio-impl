<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Doctrine\Common\Cache as DoctrineCache;
use Fusio\Engine\Cache;
use Fusio\Engine\Connector;
use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\Factory;
use Fusio\Engine\Form;
use Fusio\Engine\Parser;
use Fusio\Engine\Processor;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\Repository;
use Fusio\Engine\Response;
use Fusio\Engine\Schema;
use Fusio\Engine\Template;
use Fusio\Engine\Json;
use Fusio\Engine\Http;
use Fusio\Impl\Parser as ImplParser;
use Fusio\Impl\Repository as ImplRepository;
use Fusio\Impl\Schema as ImplSchema;

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
        $parsers = [];
        $parsers[] = new ImplParser\Database(
            $this->get('action_factory'),
            $this->get('form_element_factory'),
            $this->get('connection'),
            'fusio_action_class',
            'Fusio\Engine\ActionInterface'
        );
        $parsers[] = new Parser\Directory(
            $this->get('action_factory'),
            $this->get('form_element_factory'),
            PSX_PATH_LIBRARY . '/Action',
            'Fusio\Custom\Action',
            'Fusio\Engine\ActionInterface'
        );

        return new Parser\Composite(
            $this->get('action_factory'),
            $this->get('form_element_factory'),
            $parsers
        );
    }

    /**
     * @return \Fusio\Engine\Factory\ActionInterface
     */
    public function getActionFactory()
    {
        return new Factory\Action($this, [
            ConnectorInterface::class => 'connector',
            ProcessorInterface::class => 'processor',
            Response\FactoryInterface::class => 'engine_response',
            Template\FactoryInterface::class => 'engine_template_factory',
            Http\ClientInterface::class => 'engine_http_client',
            Json\ProcessorInterface::class => 'engine_json_processor',
            Cache\ProviderInterface::class => 'engine_cache_provider',
        ]);
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
     * @return \Fusio\Engine\Parser\ParserInterface
     */
    public function getConnectionParser()
    {
        $parsers = [];
        $parsers[] = new ImplParser\Database(
            $this->get('connection_factory'),
            $this->get('form_element_factory'),
            $this->get('connection'),
            'fusio_connection_class',
            'Fusio\Engine\ConnectionInterface'
        );

        $parsers[] = new Parser\Directory(
            $this->get('connection_factory'),
            $this->get('form_element_factory'),
            PSX_PATH_LIBRARY . '/Connection',
            'Fusio\Custom\Connection',
            'Fusio\Engine\ConnectionInterface'
        );

        return new Parser\Composite(
            $this->get('connection_factory'),
            $this->get('form_element_factory'),
            $parsers
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

    /**
     * @return \Fusio\Engine\Template\FactoryInterface
     */
    public function getEngineTemplateFactory()
    {
        return new Template\Factory(
            $this->get('config')->get('psx_debug'),
            PSX_PATH_CACHE
        );
    }

    /**
     * @return \Fusio\Engine\Json\ProcessorInterface
     */
    public function getEngineJsonProcessor()
    {
        return new Json\Processor(
            new \PSX\Data\Reader\Json(),
            new \PSX\Data\Writer\Json()
        );
    }

    /**
     * @return \Fusio\Engine\Http\ClientInterface
     */
    public function getEngineHttpClient()
    {
        return new Http\Client(
            new \PSX\Http\Client()
        );
    }

    /**
     * @return \Fusio\Engine\Cache\ProviderInterface
     */
    public function getEngineCacheProvider()
    {
        $tempDir  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'Fusio';
        $provider = new DoctrineCache\FilesystemCache($tempDir);

        return new Cache\Provider($provider);
    }
}

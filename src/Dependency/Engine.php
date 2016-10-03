<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

use Fusio\Engine\Connector;
use Fusio\Engine\Factory;
use Fusio\Engine\Form;
use Fusio\Engine\Parser;
use Fusio\Engine\Processor;
use Fusio\Engine\Repository;
use Fusio\Engine\Response;
use Fusio\Engine\Schema;
use Fusio\Engine\Template;
use Fusio\Impl\Logger;
use Fusio\Impl\Parser as ImplParser;
use Fusio\Impl\Repository as ImplRepository;
use Fusio\Impl\Schema as ImplSchema;

/**
 * Engine
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
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
        return new Factory\Action($this->get('object_builder'));
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
        return new Factory\Connection($this->get('object_builder'));
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
     * @return \Fusio\Engine\Template\FactoryInterface
     */
    public function getTemplateFactory()
    {
        return new Template\Factory(
            $this->get('config')->get('psx_debug'),
            PSX_PATH_CACHE
        );
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
    public function getResponse()
    {
        return new Response\Factory();
    }

    /**
     * @return \Fusio\Engine\LoggerInterface
     */
    public function getApiLogger()
    {
        return new Logger($this->get('connection'));
    }
}

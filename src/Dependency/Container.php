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

use Fusio\Engine\Connector;
use Fusio\Engine\Processor;
use Fusio\Engine\Response;
use Fusio\Engine\Template;
use Fusio\Impl\App;
use Fusio\Impl\Base;
use Fusio\Impl\Connector\DatabaseRepository as ConnectorDatabaseRepository;
use Fusio\Impl\Console;
use Fusio\Impl\Data\SchemaManager;
use Fusio\Impl\Factory;
use Fusio\Impl\Form;
use Fusio\Impl\Loader\DatabaseRoutes;
use Fusio\Impl\Loader\ResourceListing;
use Fusio\Impl\Loader\RoutingParser;
use Fusio\Impl\Logger;
use Fusio\Impl\Mail\Mailer;
use Fusio\Impl\Parser;
use Fusio\Impl\Processor\DatabaseRepository as ProcessorDatabaseRepository;
use Fusio\Impl\Schema;
use Fusio\Impl\User;
use Fusio\Impl\Validate;
use Monolog\Handler as LogHandler;
use PSX\Api\Console\ApiCommand;
use PSX\Data\Exporter\Popo;
use PSX\Framework\Api\CachedListing;
use PSX\Framework\Console as PSXCommand;
use PSX\Framework\Dependency\DefaultContainer;
use PSX\Schema\Console\SchemaCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command as SymfonyCommand;

/**
 * Container
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Container extends DefaultContainer
{
    use Authorization;
    use Engine;
    use Services;

    /**
     * @return \PSX\Framework\Loader\RoutingParserInterface
     */
    public function getRoutingParser()
    {
        return new DatabaseRoutes($this->get('connection'));
    }

    /**
     * @return \PSX\Framework\Loader\LocationFinderInterface
     */
    public function getLoaderLocationFinder()
    {
        return new RoutingParser($this->get('connection'));
    }

    /**
     * @return \PSX\Api\ListingInterface
     */
    public function getResourceListing()
    {
        $resourceListing = new ResourceListing($this->get('routing_parser'), $this->get('controller_factory'));

        if ($this->get('config')->get('psx_debug')) {
            return $resourceListing;
        } else {
            return new CachedListing($resourceListing, $this->get('cache'));
        }
    }

    /**
     * @return \Symfony\Component\Console\Application
     */
    public function getConsole()
    {
        $application = new Application('fusio', Base::getVersion());

        $this->appendConsoleCommands($application);

        return $application;
    }

    /**
     * @return \Fusio\Impl\Mail\MailerInterface
     */
    public function getMailer()
    {
        if ($this->get('config')->get('psx_debug') === false) {
            $transport = \Swift_MailTransport::newInstance();
        } else {
            $transport = \Swift_NullTransport::newInstance();
        }

        return new Mailer(
            $this->get('config_service'),
            $this->get('logger'),
            $transport
        );
    }

    /**
     * @return \Fusio\Impl\Logger
     */
    public function getApiLogger()
    {
        return new Logger($this->get('connection'));
    }

    protected function appendConsoleCommands(Application $application)
    {
        // psx commands
        $application->add(new PSXCommand\ContainerCommand($this));
        $application->add(new PSXCommand\RouteCommand($this->get('routing_parser')));
        $application->add(new PSXCommand\ServeCommand($this->get('config'), $this->get('dispatch'), $this->get('console_reader')));

        $application->add(new ApiCommand($this->get('api_manager'), $this->get('annotation_reader'), $this->get('config')->get('psx_json_namespace'), $this->get('config')->get('psx_url'), $this->get('config')->get('psx_dispatch')));
        $application->add(new SchemaCommand($this->get('schema_manager')));

        // fusio commands
        $application->add(new Console\Action\AddCommand($this->get('system_api_executor_service')));
        $application->add(new Console\Action\ClassCommand($this->get('action_parser')));
        $application->add(new Console\Action\DetailCommand($this->get('action_factory'), $this->get('action_repository'), $this->get('connection_repository')));
        $application->add(new Console\Action\ListCommand($this->get('action_service')));

        $application->add(new Console\App\AddCommand($this->get('system_api_executor_service')));
        $application->add(new Console\App\ListCommand($this->get('app_service')));

        $application->add(new Console\Connection\AddCommand($this->get('system_api_executor_service')));
        $application->add(new Console\Connection\ClassCommand($this->get('connection_parser')));
        $application->add(new Console\Connection\DetailCommand($this->get('connection_factory'), $this->get('action_repository'), $this->get('connection_repository')));
        $application->add(new Console\Connection\ListCommand($this->get('connection_service')));

        $application->add(new Console\Schema\AddCommand($this->get('system_api_executor_service')));
        $application->add(new Console\Schema\ExportCommand($this->get('connection')));
        $application->add(new Console\Schema\ListCommand($this->get('schema_service')));

        $application->add(new Console\System\DeployCommand($this->get('system_deploy_service'), dirname($this->getParameter('config.file')), $this->get('connection'), $this->get('logger')));
        $application->add(new Console\System\ExportCommand($this->get('system_export_service')));
        $application->add(new Console\System\ImportCommand($this->get('system_import_service'), $this->get('connection'), $this->get('logger')));
        $application->add(new Console\System\InstallCommand($this->get('connection')));
        $application->add(new Console\System\RegisterCommand($this->get('system_import_service'), $this->get('connection_service'), $this->get('connection')));
        $application->add(new Console\System\TokenCommand($this->get('app_service'), $this->get('scope_service'), $this->get('table_manager')->getTable('Fusio\Impl\Table\App'), $this->get('table_manager')->getTable('Fusio\Impl\Table\User')));
        $application->add(new Console\System\CheckCommand($this->get('connection')));

        $application->add(new Console\User\AddCommand($this->get('user_service')));
        $application->add(new Console\User\ListCommand($this->get('user_service')));

        // symfony commands
        $application->add(new SymfonyCommand\HelpCommand());
        $application->add(new SymfonyCommand\ListCommand());
    }

    protected function appendDefaultConfig()
    {
        return array_merge(parent::appendDefaultConfig(), array(
            'fusio_project_key'      => '42eec18ffdbffc9fda6110dcc705d6ce',
            'fusio_app_per_consumer' => 16,
            'fusio_app_approval'     => false,
            'fusio_grant_implicit'   => true,
            'fusio_expire_implicit'  => 'PT1H',
            'fusio_expire_app'       => 'P2D',
            'fusio_expire_backend'   => 'PT1H',
            'fusio_expire_consumer'  => 'PT1H',
        ));
    }
}

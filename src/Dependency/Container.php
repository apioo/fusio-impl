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

namespace Fusio\Impl\Dependency;

use Doctrine\DBAL;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Base;
use Fusio\Impl\Console;
use Fusio\Impl\EventListener\AuditListener;
use Fusio\Impl\EventListener\WebhookListener;
use Fusio\Impl\Framework\Api\GeneratorFactory;
use Fusio\Impl\Framework\Api\ResourceListing;
use Fusio\Impl\Framework\Filter;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Framework\Loader\LocationFinder\DatabaseFinder;
use Fusio\Impl\Framework\Loader\RoutingParser\DatabaseParser;
use Fusio\Impl\Mail;
use Fusio\Impl\Provider\ProviderLoader;
use Fusio\Impl\Provider\ProviderWriter;
use Fusio\Impl\Rpc\InvokerFactory;
use Fusio\Impl\Schema;
use Fusio\Impl\Table;
use PSX\Api\Console as ApiConsole;
use PSX\Api\GeneratorFactoryInterface;
use PSX\Api\Listing\CachedListing;
use PSX\Api\Listing\FilterFactoryInterface;
use PSX\Api\ListingInterface;
use PSX\Framework\Console as FrameworkConsole;
use PSX\Framework\Dependency\DefaultContainer;
use PSX\Framework\Loader\LocationFinderInterface;
use PSX\Framework\Loader\RoutingParserInterface;
use PSX\Schema\Console as SchemaConsole;
use PSX\Schema\Parser\TypeSchema\ImportResolver;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaManagerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command as SymfonyCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    public function getRoutingParser(): RoutingParserInterface
    {
        return new DatabaseParser($this->get('connection'));
    }

    public function getLoaderLocationFinder(): LocationFinderInterface
    {
        return new DatabaseFinder(
            $this->get('connection'),
            $this->get('table_manager')->getTable(Table\Category::class)
        );
    }

    public function getResourceListing(): ListingInterface
    {
        $resourceListing = new ResourceListing($this->get('routing_parser'), $this->get('controller_factory'));

        if ($this->get('config')->get('psx_debug')) {
            return $resourceListing;
        } else {
            return new CachedListing($resourceListing, $this->get('cache'));
        }
    }

    public function getListingFilterFactory(): FilterFactoryInterface
    {
        $filter = new Filter\FilterFactory($this->get('connection'));
        $filter->setDefault('default');

        return $filter;
    }

    public function getGeneratorFactory(): GeneratorFactoryInterface
    {
        return new GeneratorFactory(
            $this->get('table_manager')->getTable(Table\Scope::class),
            $this->get('config_service'),
            $this->get('annotation_reader_factory')->factory('PSX\Schema\Parser\Popo\Annotation'),
            $this->get('config')->get('psx_json_namespace'),
            $this->get('config')->get('psx_url'),
            $this->get('config')->get('psx_dispatch')
        );
    }

    public function getConnection(): DBAL\Connection
    {
        $params = $this->get('config')->get('psx_connection');
        $config = new DBAL\Configuration();
        $config->setSchemaAssetsFilter(static function($assetName) {
            if ($assetName instanceof AbstractAsset) {
                $assetName = $assetName->getName();
            }
            if (preg_match('~^fusio_log_(\d{8})$~', $assetName) || preg_match('~^fusio_audit_(\d{8})$~', $assetName)) {
                // ignore archive tables
                return false;
            }
            return preg_match('~^fusio_~', $assetName);
        });

        return DBAL\DriverManager::getConnection($params, $config);
    }

    public function getConsole(): Application
    {
        $application = new Application('fusio', Base::getVersion());
        $application->setHelperSet(new HelperSet($this->appendConsoleHelpers()));

        $this->appendConsoleCommands($application);

        return $application;
    }

    public function getMailer(): Mail\MailerInterface
    {
        return new Mail\Mailer(
            $this->get('config_service'),
            $this->get('connection_resolver_service'),
            $this->get('mailer_sender_factory'),
            $this->get('config'),
            $this->get('logger')
        );
    }

    public function getMailerSenderFactory(): Mail\SenderFactory
    {
        $factory = new Mail\SenderFactory();
        $factory->add(new Mail\Sender\SMTP(), 8);

        return $factory;
    }

    public function getProviderLoader(): ProviderLoader
    {
        return new ProviderLoader($this->get('connection'), $this->get('config')->get('fusio_provider'));
    }

    public function getProviderWriter(): ProviderWriter
    {
        return new ProviderWriter($this->get('connection'));
    }

    public function getSchemaParserImportResolver(): ImportResolver
    {
        $resolver = ImportResolver::createDefault($this->get('http_client'));
        $resolver->addResolver('schema', new Schema\Parser\Resolver\Database($this->get('connection')));

        return $resolver;
    }

    public function getSchemaLoader(): Schema\Loader
    {
        return new Schema\Loader(
            $this->get('connection'),
            $this->get('schema_manager')
        );
    }

    public function getSchemaManager(): SchemaManagerInterface
    {
        return new SchemaManager(
            $this->get('annotation_reader_factory')->factory('PSX\Schema\Annotation'),
            $this->get('cache'),
            $this->get('config')->get('psx_debug'),
            $this->get('schema_parser_import_resolver')
        );
    }

    public function getRpcInvokerFactory(): InvokerFactory
    {
        return new InvokerFactory(
            $this->get('action_invoker_service'),
            $this->get('table_manager')->getTable(Table\Route\Method::class),
            $this->get('schema_loader'),
            $this->get('security_token_validator'),
            $this->get('rate_service')
        );
    }

    protected function appendConsoleCommands(Application $application)
    {
        // psx commands
        $application->add(new FrameworkConsole\RouteCommand($this->get('routing_parser')));
        $application->add(new FrameworkConsole\ServeCommand($this));
        $application->add(new FrameworkConsole\Container\ListCommand($this->get('container_inspector')));
        $application->add(new FrameworkConsole\Container\BuildCommand($this, $this->get('annotation_reader_factory')->factory('PSX\Dependency\Annotation'), $this->get('config')));

        $application->add(new ApiConsole\ParseCommand($this->get('api_manager'), $this->get('generator_factory')));
        $application->add(new ApiConsole\ResourceCommand($this->get('resource_listing'), $this->get('generator_factory')));
        $application->add(new ApiConsole\GenerateCommand($this->get('resource_listing'), $this->get('generator_factory'), $this->get('listing_filter_factory')));

        $application->add(new SchemaConsole\ParseCommand($this->get('schema_manager')));

        // fusio commands
        $application->add(new Console\Action\AddCommand($this->get('action_service')));
        $application->add(new Console\Action\ClassCommand($this->get('action_parser')));
        $application->add(new Console\Action\DetailCommand($this->get('action_factory'), $this->get('action_repository'), $this->get('connection_repository')));
        $application->add(new Console\Action\ExecuteCommand($this->get('action_executor_service'), $this->get('table_manager')->getTable(Table\Action::class)));
        $application->add(new Console\Action\ListCommand($this->get('table_manager')->getTable(View\Action::class)));
        $application->add(new Console\Action\QueueCommand($this->get('action_queue_consumer_service')));

        $application->add(new Console\App\AddCommand($this->get('app_service')));
        $application->add(new Console\App\ListCommand($this->get('table_manager')->getTable(View\App::class)));

        $application->add(new Console\Connection\AddCommand($this->get('connection_service')));
        $application->add(new Console\Connection\ClassCommand($this->get('connection_parser')));
        $application->add(new Console\Connection\DetailCommand($this->get('connection_factory'), $this->get('action_repository'), $this->get('connection_repository')));
        $application->add(new Console\Connection\ListCommand($this->get('table_manager')->getTable(View\Connection::class)));

        $application->add(new Console\Cronjob\ExecuteCommand($this->get('cronjob_service')));
        $application->add(new Console\Cronjob\ListCommand($this->get('table_manager')->getTable(View\Cronjob::class)));

        $application->add(new Console\Event\ExecuteCommand($this->get('event_executor_service')));

        $application->add(new Console\Marketplace\ListCommand($this->get('marketplace_repository_remote')));
        $application->add(new Console\Marketplace\InstallCommand($this->get('marketplace_installer'), $this->get('marketplace_repository_remote')));
        $application->add(new Console\Marketplace\UpdateCommand($this->get('marketplace_installer'), $this->get('marketplace_repository_remote')));
        $application->add(new Console\Marketplace\RemoveCommand($this->get('marketplace_installer')));

        $application->add(new Console\Migration\ExecuteCommand($this->get('connection'), $this->get('connector')));
        $application->add(new Console\Migration\GenerateCommand($this->get('connection'), $this->get('connector')));
        $application->add(new Console\Migration\LatestCommand($this->get('connection'), $this->get('connector')));
        $application->add(new Console\Migration\MigrateCommand($this->get('connection'), $this->get('connector')));
        $application->add(new Console\Migration\StatusCommand($this->get('connection'), $this->get('connector')));
        $application->add(new Console\Migration\UpToDateCommand($this->get('connection'), $this->get('connector')));
        $application->add(new Console\Migration\VersionCommand($this->get('connection'), $this->get('connector')));

        $application->add(new Console\Plan\BillingRunCommand($this->get('plan_billing_run_service')));

        $application->add(new Console\Schema\AddCommand($this->get('schema_service')));
        $application->add(new Console\Schema\ExportCommand($this->get('schema_loader')));
        $application->add(new Console\Schema\ListCommand($this->get('table_manager')->getTable(View\Schema::class)));

        $application->add(new Console\System\CheckCommand($this->get('connection')));
        $application->add(new Console\System\CleanCommand($this->get('connection')));
        $application->add(new Console\System\ClearCacheCommand($this->get('cache'), $this->get('engine_cache')));
        $application->add(new Console\System\DeployCommand($this->get('system_deploy_service'), dirname($this->getParameter('config.file')), $this->get('connection'), $this->get('logger')));
        $application->add(new Console\System\ExportCommand($this->get('system_export_service')));
        $application->add(new Console\System\ImportCommand($this->get('system_import_service'), $this->get('connection'), $this->get('logger')));
        $application->add(new Console\System\LogRotateCommand($this->get('connection')));
        $application->add(new Console\System\RegisterCommand($this->get('system_import_service'), $this->get('table_manager')->getTable(View\Connection::class), $this->get('connection')));
        $application->add(new Console\System\RestoreCommand($this->get('connection')));
        $application->add(new Console\System\TokenCommand($this->get('app_token_service'), $this->get('scope_service'), $this->get('table_manager')->getTable(Table\App::class), $this->get('table_manager')->getTable(Table\User::class)));

        $application->add(new Console\User\AddCommand($this->get('user_service')));
        $application->add(new Console\User\ListCommand($this->get('table_manager')->getTable(View\User::class)));

        // symfony commands
        $application->add(new SymfonyCommand\HelpCommand());
        $application->add(new SymfonyCommand\ListCommand());
    }

    /**
     * @return array
     */
    protected function appendConsoleHelpers()
    {
        return array(
            'db' => new ConnectionHelper($this->get('connection')),
            'question' => new QuestionHelper(),
        );
    }

    protected function appendDefaultListener(EventDispatcherInterface $eventDispatcher)
    {
        parent::appendDefaultListener($eventDispatcher);

        $eventDispatcher->addSubscriber(new AuditListener($this->get('table_manager')->getTable(Table\Audit::class)));
        $eventDispatcher->addSubscriber(new WebhookListener($this->get('engine_dispatcher')));
    }

    protected function appendDefaultConfig()
    {
        return array_merge(parent::appendDefaultConfig(), array(
            'fusio_project_key'      => 'def00000f4f7679c90443d462c56b38cfff71ed3640fdad55dec8c7e0f3b213dee7c67daefc8e890a1b959ff849a09632d89b07fbf75e5fc2987df15ff0831923540548c',
            'fusio_app_per_consumer' => 16,
            'fusio_app_approval'     => false,
            'fusio_grant_implicit'   => true,
            'fusio_expire_implicit'  => 'PT1H',
            'fusio_expire_app'       => 'P2D',
            'fusio_expire_backend'   => 'PT1H',
            'fusio_expire_consumer'  => 'PT1H',

            'psx_context_factory'    => function(){
                return new Context();
            },
        ));
    }
}

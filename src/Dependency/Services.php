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

namespace Fusio\Impl\Dependency;

use Fusio\Impl\Service;
use Fusio\Impl\Table;

/**
 * Service
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
trait Services
{
    /**
     * @return \Fusio\Impl\Service\User
     */
    public function getUserService()
    {
        return new Service\User(
            $this->get('table_manager')->getTable(Table\User::class),
            $this->get('table_manager')->getTable(Table\Scope::class),
            $this->get('table_manager')->getTable(Table\App::class),
            $this->get('table_manager')->getTable(Table\User\Scope::class),
            $this->get('config_service'),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Routes
     */
    public function getRoutesService()
    {
        return new Service\Routes(
            $this->get('table_manager')->getTable(Table\Routes::class),
            $this->get('table_manager')->getTable(Table\Routes\Method::class),
            $this->get('routes_config_service'),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Routes\Method
     */
    public function getRoutesMethodService()
    {
        return new Service\Routes\Method(
            $this->get('table_manager')->getTable(Table\Routes\Method::class),
            $this->get('table_manager')->getTable(Table\Routes\Response::class),
            $this->get('table_manager')->getTable(Table\Scope\Route::class),
            $this->get('schema_loader')
        );
    }
    /**
     * @return \Fusio\Impl\Service\Routes\Config
     */
    public function getRoutesConfigService()
    {
        return new Service\Routes\Config(
            $this->get('table_manager')->getTable(Table\Routes\Method::class),
            $this->get('table_manager')->getTable(Table\Routes\Response::class),
            $this->get('scope_service'),
            $this->get('routes_deploy_service'),
            $this->get('resource_listing'),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Action
     */
    public function getActionService()
    {
        return new Service\Action(
            $this->get('table_manager')->getTable(Table\Action::class),
            $this->get('table_manager')->getTable(Table\Routes\Method::class),
            $this->get('action_factory'),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Action\Executor
     */
    public function getActionExecutorService()
    {
        return new Service\Action\Executor(
            $this->get('table_manager')->getTable(Table\Action::class),
            $this->get('processor'),
            $this->get('app_repository'),
            $this->get('user_repository')
        );
    }

    /**
     * @return \Fusio\Impl\Service\App
     */
    public function getAppService()
    {
        return new Service\App(
            $this->get('table_manager')->getTable(Table\App::class),
            $this->get('table_manager')->getTable(Table\Scope::class),
            $this->get('table_manager')->getTable(Table\App\Scope::class),
            $this->get('table_manager')->getTable(Table\App\Token::class),
            $this->get('config')->get('fusio_project_key'),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\App\Code
     */
    public function getAppCodeService()
    {
        return new Service\App\Code(
            $this->get('table_manager')->getTable(Table\App\Code::class)
        );
    }

    /**
     * @return \Fusio\Impl\Service\App\Developer
     */
    public function getAppDeveloperService()
    {
        return new Service\App\Developer(
            $this->get('app_service'),
            $this->get('table_manager')->getTable(Table\App::class),
            $this->get('table_manager')->getTable(Table\Scope::class),
            $this->get('table_manager')->getTable(Table\User\Scope::class),
            $this->get('config')->get('fusio_app_per_consumer'),
            $this->get('config')->get('fusio_app_approval')
        );
    }

    /**
     * @return \Fusio\Impl\Service\App\Grant
     */
    public function getAppGrantService()
    {
        return new Service\App\Grant(
            $this->get('table_manager')->getTable(Table\App::class),
            $this->get('table_manager')->getTable(Table\User\Grant::class),
            $this->get('table_manager')->getTable(Table\App\Token::class)
        );
    }

    /**
     * @return \Fusio\Impl\Service\Config
     */
    public function getConfigService()
    {
        return new Service\Config(
            $this->get('table_manager')->getTable(Table\Config::class),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Connection
     */
    public function getConnectionService()
    {
        return new Service\Connection(
            $this->get('table_manager')->getTable(Table\Connection::class),
            $this->get('connection_factory'),
            $this->get('config')->get('fusio_project_key'),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Cronjob
     */
    public function getCronjobService()
    {
        return new Service\Cronjob(
            $this->get('table_manager')->getTable(Table\Cronjob::class),
            $this->get('table_manager')->getTable(Table\Cronjob\Error::class),
            $this->get('action_executor_service'),
            $this->get('config')->get('fusio_cron_file'),
            $this->get('config')->get('fusio_cron_exec'),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\System\ApiExecutor
     */
    public function getSystemApiExecutorService()
    {
        return new Service\System\ApiExecutor(
            $this->get('dispatch'),
            $this->get('connection'),
            $this->get('logger')
        );
    }

    /**
     * @return \Fusio\Impl\Service\System\Import
     */
    public function getSystemImportService()
    {
        return new Service\System\Import(
            $this->get('system_api_executor_service'),
            $this->get('connection'),
            $this->get('action_parser'),
            $this->get('connection_parser'),
            $this->get('logger')
        );
    }

    /**
     * @return \Fusio\Impl\Service\System\Export
     */
    public function getSystemExportService()
    {
        return new Service\System\Export(
            $this->get('system_api_executor_service'),
            $this->get('connection'),
            $this->get('action_parser'),
            $this->get('connection_parser'),
            $this->get('logger')
        );
    }

    /**
     * @return \Fusio\Impl\Service\System\Deploy
     */
    public function getSystemDeployService()
    {
        return new Service\System\Deploy(
            $this->get('system_import_service'),
            $this->get('system_migration_service')
        );
    }

    /**
     * @return \Fusio\Impl\Service\System\Migration
     */
    public function getSystemMigrationService()
    {
        return new Service\System\Migration(
            $this->get('connector'),
            $this->get('table_manager')->getTable(Table\Deploy\Migration::class),
            $this->get('logger')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Schema
     */
    public function getSchemaService()
    {
        return new Service\Schema(
            $this->get('table_manager')->getTable(Table\Schema::class),
            $this->get('table_manager')->getTable(Table\Routes\Method::class),
            $this->get('schema_parser'),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Scope
     */
    public function getScopeService()
    {
        return new Service\Scope(
            $this->get('table_manager')->getTable(Table\Scope::class),
            $this->get('table_manager')->getTable(Table\Scope\Route::class),
            $this->get('table_manager')->getTable(Table\App\Scope::class),
            $this->get('table_manager')->getTable(Table\User\Scope::class),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\Routes\Deploy
     */
    public function getRoutesDeployService()
    {
        return new Service\Routes\Deploy(
            $this->get('table_manager')->getTable(Table\Routes\Method::class),
            $this->get('table_manager')->getTable(Table\Routes\Response::class),
            $this->get('table_manager')->getTable(Table\Schema::class),
            $this->get('table_manager')->getTable(Table\Action::class)
        );
    }

    /**
     * @return \Fusio\Impl\Service\Rate
     */
    public function getRateService()
    {
        return new Service\Rate(
            $this->get('table_manager')->getTable(Table\Rate::class),
            $this->get('table_manager')->getTable(Table\Rate\Allocation::class),
            $this->get('table_manager')->getTable(Table\Log::class),
            $this->get('event_dispatcher')
        );
    }

    /**
     * @return \Fusio\Impl\Service\User\Activate
     */
    public function getUserActivateService()
    {
        return new Service\User\Activate(
            $this->get('user_service'),
            $this->get('config')
        );
    }

    /**
     * @return \Fusio\Impl\Service\User\TokenIssuer
     */
    public function getUserTokenIssuerService()
    {
        return new Service\User\TokenIssuer(
            $this->get('app_service'),
            $this->get('table_manager')->getTable(Table\User::class),
            $this->get('config')
        );
    }

    /**
     * @return \Fusio\Impl\Service\User\Login
     */
    public function getUserLoginService()
    {
        return new Service\User\Login(
            $this->get('user_service'),
            $this->get('user_token_issuer_service')
        );
    }

    /**
     * @return \Fusio\Impl\Service\User\Provider
     */
    public function getUserProviderService()
    {
        return new Service\User\Provider(
            $this->get('user_service'),
            $this->get('config_service'),
            $this->get('user_token_issuer_service'),
            $this->get('http_client'),
            $this->get('config')
        );
    }

    /**
     * @return \Fusio\Impl\Service\User\Register
     */
    public function getUserRegisterService()
    {
        return new Service\User\Register(
            $this->get('user_service'),
            $this->get('config_service'),
            $this->get('http_client'),
            $this->get('mailer'),
            $this->get('config')
        );
    }

    /**
     * @return \Fusio\Impl\Service\User\Authorize
     */
    public function getUserAuthorizeService()
    {
        return new Service\User\Authorize(
            $this->get('app_service'),
            $this->get('scope_service'),
            $this->get('app_code_service'),
            $this->get('table_manager')->getTable(Table\User\Grant::class),
            $this->get('config')
        );
    }
}

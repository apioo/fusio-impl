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

use Fusio\Engine\Payment;
use Fusio\Engine\User;
use Fusio\Impl\Deploy\EnvProperties;
use Fusio\Impl\Provider\ProviderConfig;
use Fusio\Impl\Provider\ProviderFactory;
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
    public function getUserService(): Service\User
    {
        return new Service\User(
            $this->get('table_manager')->getTable(Table\User::class),
            $this->get('table_manager')->getTable(Table\Scope::class),
            $this->get('table_manager')->getTable(Table\App::class),
            $this->get('table_manager')->getTable(Table\User\Scope::class),
            $this->get('config_service'),
            $this->get('event_dispatcher'),
            $this->get('config')->get('fusio_user_attributes')
        );
    }

    public function getRoutesService(): Service\Route
    {
        return new Service\Route(
            $this->get('table_manager')->getTable(Table\Route::class),
            $this->get('table_manager')->getTable(Table\Route\Method::class),
            $this->get('scope_service'),
            $this->get('routes_config_service'),
            $this->get('event_dispatcher')
        );
    }

    public function getRoutesMethodService(): Service\Route\Method
    {
        return new Service\Route\Method(
            $this->get('table_manager')->getTable(Table\Route\Method::class),
            $this->get('table_manager')->getTable(Table\Route\Response::class),
            $this->get('table_manager')->getTable(Table\Scope\Route::class),
            $this->get('schema_loader')
        );
    }

    public function getRoutesConfigService(): Service\Route\Config
    {
        return new Service\Route\Config(
            $this->get('table_manager')->getTable(Table\Route\Method::class),
            $this->get('table_manager')->getTable(Table\Route\Response::class),
            $this->get('resource_listing'),
            $this->get('event_dispatcher')
        );
    }

    public function getRoutesProviderService(): Service\Route\Provider
    {
        $factory = new ProviderFactory(
            $this->get('provider_loader'),
            $this->get('container_autowire_resolver'),
            ProviderConfig::TYPE_ROUTES,
            \Fusio\Engine\Routes\ProviderInterface::class
        );

        return new Service\Route\Provider(
            $this->get('connection'),
            $factory,
            $this,
            $this->get('routes_service'),
            $this->get('schema_service'),
            $this->get('action_service'),
            $this->get('form_element_factory'),
            $this->get('schema_manager')
        );
    }

    public function getSecurityTokenValidator(): Service\Security\TokenValidator
    {
        return new Service\Security\TokenValidator(
            $this->get('connection'),
            $this->get('config')->get('fusio_project_key'),
            $this->get('app_repository'),
            $this->get('user_repository')
        );
    }

    public function getActionService(): Service\Action
    {
        return new Service\Action(
            $this->get('table_manager')->getTable(Table\Action::class),
            $this->get('table_manager')->getTable(Table\Route\Method::class),
            $this->get('action_factory'),
            $this->get('config'),
            $this->get('event_dispatcher')
        );
    }

    public function getActionExecutorService(): Service\Action\Executor
    {
        return new Service\Action\Executor(
            $this->get('processor'),
            $this->get('app_repository'),
            $this->get('user_repository')
        );
    }

    public function getActionInvokerService(): Service\Action\Invoker
    {
        return new Service\Action\Invoker(
            $this->get('processor'),
            $this->get('plan_payer_service'),
            $this->get('config')
        );
    }

    public function getAppService(): Service\App
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

    public function getAppCodeService(): Service\App\Code
    {
        return new Service\App\Code(
            $this->get('table_manager')->getTable(Table\App\Code::class)
        );
    }

    public function getAppGrantService(): Service\App\Grant
    {
        return new Service\App\Grant(
            $this->get('table_manager')->getTable(Table\App::class),
            $this->get('table_manager')->getTable(Table\User\Grant::class),
            $this->get('table_manager')->getTable(Table\App\Token::class)
        );
    }

    public function getAppTokenService(): Service\App\Token
    {
        return new Service\App\Token(
            $this->get('table_manager')->getTable(Table\App::class),
            $this->get('table_manager')->getTable(Table\User::class),
            $this->get('table_manager')->getTable(Table\App\Token::class),
            $this->get('config'),
            $this->get('event_dispatcher')
        );
    }

    public function getConfigService(): Service\Config
    {
        return new Service\Config(
            $this->get('table_manager')->getTable(Table\Config::class),
            $this->get('event_dispatcher')
        );
    }

    public function getConnectionService(): Service\Connection
    {
        return new Service\Connection(
            $this->get('table_manager')->getTable(Table\Connection::class),
            $this->get('connection_factory'),
            $this->get('config')->get('fusio_project_key'),
            $this->get('event_dispatcher')
        );
    }

    public function getConnectionResolverService(): Service\Connection\Resolver
    {
        return new Service\Connection\Resolver(
            $this->get('connector'),
            $this->get('config_service')
        );
    }

    public function getCronjobService(): Service\Cronjob
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

    public function getConsumerAppService(): Service\Consumer\App
    {
        return new Service\Consumer\App(
            $this->get('app_service'),
            $this->get('config_service'),
            $this->get('table_manager')->getTable(Table\App::class),
            $this->get('table_manager')->getTable(Table\Scope::class),
            $this->get('table_manager')->getTable(Table\User\Scope::class)
        );
    }

    public function getConsumerSubscriptionService(): Service\Consumer\Subscription
    {
        return new Service\Consumer\Subscription(
            $this->get('event_subscription_service'),
            $this->get('config_service'),
            $this->get('table_manager')->getTable(Table\Event\Subscription::class),
            $this->get('table_manager')->getTable(Table\Event::class)
        );
    }

    public function getConsumerUserService(): Service\Consumer\User
    {
        return new Service\Consumer\User(
            $this->get('user_service')
        );
    }

    public function getEventService(): Service\Event
    {
        return new Service\Event(
            $this->get('table_manager')->getTable(Table\Event::class),
            $this->get('event_dispatcher')
        );
    }

    public function getEventExecutorService(): Service\Event\Executor
    {
        return new Service\Event\Executor(
            $this->get('table_manager')->getTable(Table\Event\Trigger::class),
            $this->get('table_manager')->getTable(Table\Event\Subscription::class),
            $this->get('table_manager')->getTable(Table\Event\Response::class),
            $this->get('http_client'),
            $this->get('connection_resolver_service'),
            $this->get('event_sender_factory_service')
        );
    }

    public function getEventSenderFactoryService(): Service\Event\SenderFactory
    {
        $factory = new Service\Event\SenderFactory();
        $factory->add(new Service\Event\Sender\HTTP(), 24);
        $factory->add(new Service\Event\Sender\Guzzle(), 16);
        $factory->add(new Service\Event\Sender\Noop(), -32);

        return $factory;
    }

    public function getEventSubscriptionService(): Service\Event\Subscription
    {
        return new Service\Event\Subscription(
            $this->get('table_manager')->getTable(Table\Event::class),
            $this->get('table_manager')->getTable(Table\Event\Subscription::class),
            $this->get('event_dispatcher')
        );
    }

    public function getSystemApiExecutorService(): Service\System\ApiExecutor
    {
        return new Service\System\ApiExecutor(
            $this->get('dispatch'),
            $this->get('connection'),
            $this->get('logger')
        );
    }

    public function getSystemImportService(): Service\System\Import
    {
        return new Service\System\Import(
            $this->get('system_api_executor_service'),
            $this->get('connection'),
            $this->get('action_parser'),
            $this->get('connection_parser'),
            $this->get('logger'),
            $this->get('provider_writer')
        );
    }

    public function getSystemExportService(): Service\System\Export
    {
        return new Service\System\Export(
            $this->get('system_api_executor_service'),
            $this->get('connection'),
            $this->get('action_parser'),
            $this->get('connection_parser'),
            $this->get('logger')
        );
    }

    public function getSystemDeployService(): Service\System\Deploy
    {
        return new Service\System\Deploy(
            $this->get('system_import_service'),
            $this->get('system_web_server_service'),
            new EnvProperties($this->get('config')),
            $this->get('schema_parser_import_resolver')
        );
    }

    public function getSystemWebServerService(): Service\System\WebServer
    {
        return new Service\System\WebServer(
            $this->get('config')
        );
    }

    public function getSchemaService(): Service\Schema
    {
        return new Service\Schema(
            $this->get('table_manager')->getTable(Table\Schema::class),
            $this->get('table_manager')->getTable(Table\Route\Method::class),
            $this->get('schema_loader'),
            $this->get('event_dispatcher')
        );
    }

    public function getScopeService(): Service\Scope
    {
        return new Service\Scope(
            $this->get('table_manager')->getTable(Table\Scope::class),
            $this->get('table_manager')->getTable(Table\Scope\Route::class),
            $this->get('table_manager')->getTable(Table\App\Scope::class),
            $this->get('table_manager')->getTable(Table\User\Scope::class),
            $this->get('event_dispatcher')
        );
    }

    public function getSdkService(): Service\Sdk
    {
        return new Service\Sdk(
            $this->get('console'),
            $this->get('config')
        );
    }

    public function getRateService(): Service\Rate
    {
        return new Service\Rate(
            $this->get('table_manager')->getTable(Table\Rate::class),
            $this->get('table_manager')->getTable(Table\Rate\Allocation::class),
            $this->get('table_manager')->getTable(Table\Log::class),
            $this->get('event_dispatcher')
        );
    }

    public function getLogService(): Service\Log
    {
        return new Service\Log(
            $this->get('connection')
        );
    }

    public function getMarketplaceRepositoryRemote(): Service\Marketplace\RepositoryInterface
    {
        return new Service\Marketplace\Repository\Remote(
            $this->get('http_client'),
            $this->get('config')->get('fusio_marketplace_url') ?: ''
        );
    }

    public function getMarketplaceRepositoryLocal(): Service\Marketplace\RepositoryInterface
    {
        return new Service\Marketplace\Repository\Local(
            $this->get('config')->get('fusio_apps_dir') ?: $this->get('config')->get('psx_path_public')
        );
    }

    public function getMarketplaceInstaller(): Service\Marketplace\Installer
    {
        return new Service\Marketplace\Installer(
            $this->get('marketplace_repository_local'),
            $this->get('marketplace_repository_remote'),
            $this->get('config')
        );
    }

    public function getPlanService(): Service\Plan
    {
        return new Service\Plan(
            $this->get('table_manager')->getTable(Table\Plan::class),
            $this->get('event_dispatcher')
        );
    }

    public function getPlanContractService(): Service\Plan\Contract
    {
        return new Service\Plan\Contract(
            $this->get('table_manager')->getTable(Table\Plan\Contract::class),
            $this->get('table_manager')->getTable(Table\Plan\Invoice::class),
            $this->get('event_dispatcher')
        );
    }

    public function getPlanInvoiceService(): Service\Plan\Invoice
    {
        return new Service\Plan\Invoice(
            $this->get('table_manager')->getTable(Table\Plan\Contract::class),
            $this->get('table_manager')->getTable(Table\Plan\Invoice::class),
            $this->get('table_manager')->getTable(Table\User::class),
            $this->get('event_dispatcher')
        );
    }

    public function getPlanOrderService(): Service\Plan\Order
    {
        return new Service\Plan\Order(
            $this->get('plan_contract_service'),
            $this->get('plan_invoice_service'),
            $this->get('table_manager')->getTable(Table\Plan::class),
            $this->get('event_dispatcher')
        );
    }

    public function getPlanBillingRunService(): Service\Plan\BillingRun
    {
        return new Service\Plan\BillingRun(
            $this->get('plan_invoice_service'),
            $this->get('table_manager')->getTable(Table\Plan\Contract::class),
            $this->get('table_manager')->getTable(Table\Plan\Invoice::class),
            $this->get('table_manager')->getTable(Table\User::class),
            $this->get('event_dispatcher')
        );
    }

    public function getPlanPayerService(): Service\Plan\Payer
    {
        return new Service\Plan\Payer(
            $this->get('table_manager')->getTable(Table\User::class),
            $this->get('table_manager')->getTable(Table\Plan\Usage::class),
            $this->get('event_dispatcher')
        );
    }

    public function getTransactionService(): Service\Transaction
    {
        $factory = new ProviderFactory(
            $this->get('provider_loader'),
            $this->get('container_autowire_resolver'),
            ProviderConfig::TYPE_PAYMENT,
            Payment\ProviderInterface::class
        );

        return new Service\Transaction(
            $this->get('connector'),
            $this->get('plan_invoice_service'),
            $factory,
            $this->get('config'),
            $this->get('table_manager')->getTable(Table\Plan\Invoice::class),
            $this->get('table_manager')->getTable(Table\Transaction::class),
            $this->get('event_dispatcher')
        );
    }

    public function getUserActivateService(): Service\User\Activate
    {
        return new Service\User\Activate(
            $this->get('user_service'),
            $this->get('user_token_service')
        );
    }

    public function getUserLoginService(): Service\User\Login
    {
        return new Service\User\Login(
            $this->get('user_service'),
            $this->get('app_token_service'),
            $this->get('config')
        );
    }

    public function getUserProviderService(): Service\User\Provider
    {
        $factory = new ProviderFactory(
            $this->get('provider_loader'),
            $this->get('container_autowire_resolver'),
            ProviderConfig::TYPE_USER,
            User\ProviderInterface::class
        );

        return new Service\User\Provider(
            $this->get('user_service'),
            $this->get('app_token_service'),
            $factory,
            $this->get('config')
        );
    }

    public function getUserRegisterService(): Service\User\Register
    {
        return new Service\User\Register(
            $this->get('user_service'),
            $this->get('user_captcha_service'),
            $this->get('user_token_service'),
            $this->get('user_mailer_service'),
            $this->get('config_service')
        );
    }

    public function getUserResetPasswordService(): Service\User\ResetPassword
    {
        return new Service\User\ResetPassword(
            $this->get('user_service'),
            $this->get('user_captcha_service'),
            $this->get('user_token_service'),
            $this->get('user_mailer_service'),
            $this->get('table_manager')->getTable(Table\User::class)
        );
    }

    public function getUserAuthorizeService(): Service\User\Authorize
    {
        return new Service\User\Authorize(
            $this->get('app_token_service'),
            $this->get('scope_service'),
            $this->get('app_code_service'),
            $this->get('table_manager')->getTable(Table\App::class),
            $this->get('table_manager')->getTable(Table\User\Grant::class),
            $this->get('config')
        );
    }

    public function getUserCaptchaService(): Service\User\Captcha
    {
        return new Service\User\Captcha(
            $this->get('config_service'),
            $this->get('http_client')
        );
    }

    public function getUserMailerService(): Service\User\Mailer
    {
        return new Service\User\Mailer(
            $this->get('config_service'),
            $this->get('mailer')
        );
    }

    public function getUserTokenService(): Service\User\Token
    {
        return new Service\User\Token(
            $this->get('table_manager')->getTable(Table\User::class),
            $this->get('config')
        );
    }

    public function getHealthService(): Service\Health
    {
        return new Service\Health(
            $this->get('connection_service'),
            $this->get('table_manager')->getTable(Table\Connection::class),
            $this->get('connection_factory'),
            $this->get('config')->get('fusio_project_key')
        );
    }
}

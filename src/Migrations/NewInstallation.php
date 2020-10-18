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

namespace Fusio\Impl\Migrations;

use Fusio\Adapter;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Action\Welcome;
use Fusio\Impl\Authorization;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Backend;
use Fusio\Impl\Connection\System as ConnectionSystem;
use Fusio\Impl\Consumer;
use Fusio\Impl\Model\Collection_Category_Query;
use Fusio\Impl\Model\Collection_Query;
use Fusio\Impl\System;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Model\Form_Container;
use Fusio\Impl\Model\Message;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Framework\Controller\Generator;
use PSX\Framework\Controller\Tool;
use PSX\Framework\Schema\Passthru;

/**
 * NewInstallation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class NewInstallation
{
    private static $data;

    public static function getData()
    {
        if (self::$data) {
            return self::$data;
        }

        $backendAppKey     = TokenGenerator::generateAppKey();
        $backendAppSecret  = TokenGenerator::generateAppSecret();
        $consumerAppKey    = TokenGenerator::generateAppKey();
        $consumerAppSecret = TokenGenerator::generateAppSecret();
        $password          = \password_hash(TokenGenerator::generateUserPassword(), PASSWORD_DEFAULT);

        $now = new \DateTime();

        $config = [
            'default' => [
                '/' => [
                    'GET' => new Method(Welcome::class, null, [200 => Passthru::class]),
                ]
            ],
            'backend' => [
                '/account' => [
                    'GET' => new Method(Backend\Action\Account\Get::class, null, [200 => Backend\Model\User::class], null, 'backend.account'),
                    'PUT' => new Method(Backend\Action\Account\Update::class, Backend\Model\User_Update::class, [200 => Message::class], null, 'backend.account'),
                ],
                '/account/change_password' => [
                    'PUT' => new Method(Backend\Action\Account\ChangePassword::class, Backend\Model\Account_ChangePassword::class, [200 => Message::class], null, 'backend.account'),
                ],
                '/action' => [
                    'GET' => new Method(Backend\Action\Action\GetAll::class, null, [200 => Backend\Model\Action_Collection::class], Collection_Category_Query::class, 'backend.action'),
                    'POST' => new Method(Backend\Action\Action\Create::class, Backend\Model\Action_Create::class, [200 => Message::class], null, 'backend.action', 'fusio.action.create'),
                ],
                '/action/list' => [
                    'GET' => new Method(Backend\Action\Action\GetIndex::class, null, [200 => Backend\Model\Action_Index::class], null, 'backend.action'),
                ],
                '/action/form' => [
                    'PUT' => new Method(Backend\Action\Action\Form::class, '', [], null, 'backend.action'),
                ],
                '/action/execute/$action_id<[0-9]+>' => [
                    'POST' => new Method(Backend\Action\Action\Execute::class, Backend\Model\Action_Execute_Request::class, [200 => Backend\Model\Action_Execute_Response::class], null, 'backend.action'),
                ],
                '/action/$action_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Action\Get::class, null, [200 => Backend\Model\Action::class], null, 'backend.action'),
                    'PUT' => new Method(Backend\Action\Action\Update::class, Backend\Model\Action_Update::class, [200 => Message::class], null, 'backend.action', 'fusio.action.update'),
                    'DELETE' => new Method(Backend\Action\Action\Delete::class, null, [200 => Message::class], null, 'backend.action', 'fusio.action.delete'),
                ],
                '/app/token' => [
                    'GET' => new Method(Backend\Action\App\Token\GetAll::class, null, [200 => Backend\Model\App_Token_Collection::class], Backend\Model\App_Token_Collection_Query::class, 'backend.app'),
                ],
                '/app/token/$token_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\App\Token\Get::class, null, [200 => Backend\Model\App_Token::class], null, 'backend.app'),
                ],
                '/app' => [
                    'GET' => new Method(Backend\Action\App\GetAll::class, null, [200 => Backend\Model\App_Collection::class], Collection_Query::class, 'backend.app'),
                    'POST' => new Method(Backend\Action\App\Create::class, Backend\Model\App_Create::class, [200 => Message::class], null, 'backend.app'),
                ],
                '/app/$app_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\App\Get::class, null, [200 => Backend\Model\App::class], null, 'backend.app'),
                    'PUT' => new Method(Backend\Action\App\Update::class, Backend\Model\App_Update::class, [200 => Message::class], null, 'backend.app'),
                    'DELETE' => new Method(Backend\Action\App\Delete::class, null, [200 => Message::class], null, 'backend.app'),
                ],
                '/app/$app_id<[0-9]+>/token/:token_id' => [
                    'DELETE' => new Method(Backend\Action\App\DeleteToken::class, null, [200 => Message::class], null, 'backend.app'),
                ],
                '/audit' => [
                    'GET' => new Method(Backend\Action\Audit\GetAll::class, null, [200 => Backend\Model\Audit_Collection::class], Backend\Model\Audit_Collection_Query::class, 'backend.audit'),
                ],
                '/audit/$audit_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Audit\Get::class, null, [200 => Backend\Model\Audit::class], null, 'backend.audit'),
                ],
                '/config' => [
                    'GET' => new Method(Backend\Action\Config\GetAll::class, null, [200 => Backend\Model\Config_Collection::class], Collection_Query::class, 'backend.config'),
                ],
                '/config/$config_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Config\Get::class, null, [200 => Backend\Model\Config::class], null, 'backend.config'),
                    'PUT' => new Method(Backend\Action\Config\Update::class, Backend\Model\Config_Update::class, [200 => Message::class], null, 'backend.config'),
                ],
                '/connection' => [
                    'GET' => new Method(Backend\Action\Connection\GetAll::class, null, [200 => Backend\Model\Connection_Collection::class], Collection_Query::class, 'backend.connection'),
                    'POST' => new Method(Backend\Action\Connection\Create::class, Backend\Model\Connection_Create::class, [200 => Message::class], null, 'backend.connection'),
                ],
                '/connection/list' => [
                    'GET' => new Method(Backend\Action\Connection\GetIndex::class, null, [200 => Backend\Model\Connection_Index::class], null, 'backend.connection'),
                ],
                '/connection/form' => [
                    'PUT' => new Method(Backend\Action\Connection\GetForm::class, '', [], null, 'backend.connection'),
                ],
                '/connection/$connection_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Connection\Get::class, null, [200 => Backend\Model\Connection::class], null, 'backend.connection'),
                    'PUT' => new Method(Backend\Action\Connection\Update::class, Backend\Model\Connection_Update::class, [200 => Message::class], null, 'backend.connection'),
                    'DELETE' => new Method(Backend\Action\Connection\Delete::class, null, [200 => Message::class], null, 'backend.connection'),
                ],
                '/cronjob' => [
                    'GET' => new Method(Backend\Action\Cronjob\GetAll::class, null, [200 => Backend\Model\Cronjob_Collection::class], Collection_Category_Query::class, 'backend.cronjob'),
                    'POST' => new Method(Backend\Action\Cronjob\Create::class, Backend\Model\Cronjob_Create::class, [200 => Message::class], null, 'backend.cronjob'),
                ],
                '/cronjob/$cronjob_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Cronjob\Get::class, null, [200 => Backend\Model\Cronjob::class], null, 'backend.cronjob'),
                    'PUT' => new Method(Backend\Action\Cronjob\Update::class, Backend\Model\Cronjob_Update::class, [200 => Message::class], null, 'backend.cronjob'),
                    'DELETE' => new Method(Backend\Action\Cronjob\Delete::class, null, [200 => Message::class], null, 'backend.cronjob'),
                ],
                '/dashboard' => [
                    'GET' => new Method(Backend\Action\Dashboard\GetAll::class, null, [200 => Backend\Model\Dashboard::class], null, 'backend.dashboard'),
                ],
                '/event/subscription' => [
                    'GET' => new Method(Backend\Action\Event\Subscription\GetAll::class, null, [200 => Backend\Model\Event_Subscription_Collection::class], Collection_Query::class, 'backend.event'),
                    'POST' => new Method(Backend\Action\Event\Subscription\Create::class, Backend\Model\Event_Subscription_Create::class, [200 => Message::class], null, 'backend.event'),
                ],
                '/event/subscription/$subscription_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Event\Subscription\Get::class, null, [200 => Backend\Model\Event_Subscription::class], null, 'backend.event'),
                    'PUT' => new Method(Backend\Action\Event\Subscription\Update::class, Backend\Model\Event_Subscription_Update::class, [200 => Message::class], null, 'backend.event'),
                    'DELETE' => new Method(Backend\Action\Event\Subscription\Delete::class, null, [200 => Message::class], null, 'backend.event'),
                ],
                '/event' => [
                    'GET' => new Method(Backend\Action\Event\GetAll::class, null, [200 => Backend\Model\Event_Collection::class], Collection_Category_Query::class, 'backend.event'),
                    'POST' => new Method(Backend\Action\Event\Create::class, Backend\Model\Event_Create::class, [200 => Message::class], null, 'backend.event'),
                ],
                '/event/$event_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Event\Get::class, null, [200 => Backend\Model\Event::class], null, 'backend.event'),
                    'PUT' => new Method(Backend\Action\Event\Update::class, Backend\Model\Event_Update::class, [200 => Message::class], null, 'backend.event'),
                    'DELETE' => new Method(Backend\Action\Event\Delete::class, null, [200 => Message::class], null, 'backend.event'),
                ],
                '/import/process' => [
                    'POST' => new Method(Backend\Action\Import\Format::class, Backend\Model\Import_Request::class, [200 => Backend\Model\Adapter::class], null, 'backend.import'),
                ],
                '/import/:format' => [
                    'POST' => new Method(Backend\Action\Import\Process::class, Backend\Model\Adapter::class, [200 => Backend\Model\Import_Response::class], null, 'backend.import'),
                ],
                '/log/error' => [
                    'GET' => new Method(Backend\Action\Log\Error\GetAll::class, null, [200 => Backend\Model\Log_Error_Collection::class], Collection_Query::class, 'backend.log'),
                ],
                '/log/error/$error_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Log\Error\Get::class, null, [200 => Backend\Model\Log_Error::class], null, 'backend.log'),
                ],
                '/log' => [
                    'GET' => new Method(Backend\Action\Log\GetAll::class, null, [200 => Backend\Model\Log_Collection::class], Backend\Model\Log_Collection_Query::class, 'backend.log'),
                ],
                '/log/$error_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Log\Get::class, null, [200 => Backend\Model\Log::class], null, 'backend.log'),
                ],
                '/marketplace' => [
                    'GET' => new Method(Backend\Action\Marketplace\GetAll::class, null, [200 => Backend\Model\Marketplace_Collection::class], null, 'backend.marketplace'),
                    'POST' => new Method(Backend\Action\Marketplace\Install::class, Backend\Model\Marketplace_Install::class, [200 => Backend\Model\Marketplace_Install::class], null, 'backend.marketplace'),
                ],
                '/marketplace/:app_name' => [
                    'GET' => new Method(Backend\Action\Marketplace\Get::class, null, [200 => Backend\Model\Marketplace_Local_App::class], null, 'backend.marketplace'),
                    'PUT' => new Method(Backend\Action\Marketplace\Update::class, null, [200 => Message::class], null, 'backend.marketplace'),
                    'DELETE' => new Method(Backend\Action\Marketplace\Remove::class, null, [200 => Message::class], null, 'backend.marketplace'),
                ],
                '/plan/contract' => [
                    'GET' => new Method(Backend\Action\Plan\Contract\GetAll::class, null, [200 => Backend\Model\Plan_Contract_Collection::class], Collection_Query::class, 'backend.plan'),
                    'POST' => new Method(Backend\Action\Plan\Contract\Create::class, Backend\Model\Plan_Contract_Create::class, [200 => Message::class], null, 'backend.plan'),
                ],
                '/plan/contract/$contract_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Plan\Contract\Get::class, null, [200 => Backend\Model\Plan_Contract::class], null, 'backend.plan'),
                    'PUT' => new Method(Backend\Action\Plan\Contract\Update::class, Backend\Model\Plan_Contract_Update::class, [200 => Message::class], null, 'backend.plan'),
                    'DELETE' => new Method(Backend\Action\Plan\Contract\Delete::class, null, [200 => Message::class], null, 'backend.plan'),
                ],
                '/plan/invoice' => [
                    'GET' => new Method(Backend\Action\Plan\Invoice\GetAll::class, null, [200 => Backend\Model\Plan_Invoice_Collection::class], Collection_Query::class, 'backend.plan'),
                    'POST' => new Method(Backend\Action\Plan\Invoice\Create::class, Backend\Model\Plan_Invoice_Create::class, [200 => Message::class], null, 'backend.plan'),
                ],
                '/plan/invoice/$invoice_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Plan\Invoice\Get::class, null, [200 => Backend\Model\Plan_Invoice::class], null, 'backend.plan'),
                    'PUT' => new Method(Backend\Action\Plan\Invoice\Update::class, Backend\Model\Plan_Invoice_Update::class, [200 => Message::class], null, 'backend.plan'),
                    'DELETE' => new Method(Backend\Action\Plan\Invoice\Delete::class, null, [200 => Message::class], null, 'backend.plan'),
                ],
                '/plan' => [
                    'GET' => new Method(Backend\Action\Plan\GetAll::class, null, [200 => Backend\Model\Plan_Collection::class], Collection_Query::class, 'backend.plan'),
                    'POST' => new Method(Backend\Action\Plan\Create::class, Backend\Model\Plan_Create::class, [200 => Message::class], null, 'backend.plan'),
                ],
                '/plan/$plan_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Plan\Get::class, null, [200 => Backend\Model\Plan::class], null, 'backend.plan'),
                    'PUT' => new Method(Backend\Action\Plan\Update::class, Backend\Model\Plan_Update::class, [200 => Message::class], null, 'backend.plan'),
                    'DELETE' => new Method(Backend\Action\Plan\Delete::class, null, [200 => Message::class], null, 'backend.plan'),
                ],
                '/rate' => [
                    'GET' => new Method(Backend\Action\Rate\GetAll::class, null, [200 => Backend\Model\Rate_Collection::class], Collection_Query::class, 'backend.rate'),
                    'POST' => new Method(Backend\Action\Rate\Create::class, Backend\Model\Rate_Create::class, [200 => Message::class], null, 'backend.rate'),
                ],
                '/rate/$rate_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Rate\Get::class, null, [200 => Backend\Model\Rate::class], null, 'backend.rate'),
                    'PUT' => new Method(Backend\Action\Rate\Update::class, Backend\Model\Rate_Update::class, [200 => Message::class], null, 'backend.rate'),
                    'DELETE' => new Method(Backend\Action\Rate\Delete::class, null, [200 => Message::class], null, 'backend.rate'),
                ],
                '/routes' => [
                    'GET' => new Method(Backend\Action\Route\GetAll::class, null, [200 => Backend\Model\Route_Collection::class], Collection_Category_Query::class, 'backend.route'),
                    'POST' => new Method(Backend\Action\Route\Create::class, Backend\Model\Route_Create::class, [200 => Message::class], null, 'backend.route'),
                ],
                '/routes/provider' => [
                    'GET' => new Method(Backend\Action\Route\Provider\Index::class, null, [200 => Backend\Model\Route_Index_Providers::class], null, 'backend.route'),
                ],
                '/routes/provider/:provider' => [
                    'GET' => new Method(Backend\Action\Route\Provider\Form::class, null, [200 => Form_Container::class], null, 'backend.route'),
                    'POST' => new Method(Backend\Action\Route\Provider\Create::class, Backend\Model\Route_Provider::class, [200 => Message::class], null, 'backend.route'),
                    'PUT' => new Method(Backend\Action\Route\Provider\Changelog::class, Backend\Model\Route_Provider_Config::class, [200 => Backend\Model\Route_Provider_Changelog::class], null, 'backend.route'),
                ],
                '/routes/$route_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Route\Get::class, null, [200 => Backend\Model\Route::class], null, 'backend.route'),
                    'PUT' => new Method(Backend\Action\Route\Update::class, Backend\Model\Route_Update::class, [200 => Message::class], null, 'backend.route'),
                    'DELETE' => new Method(Backend\Action\Route\Delete::class, null, [200 => Message::class], null, 'backend.route'),
                ],
                '/schema' => [
                    'GET' => new Method(Backend\Action\Schema\GetAll::class, null, [200 => Backend\Model\Schema_Collection::class], Collection_Category_Query::class, 'backend.schema'),
                    'POST' => new Method(Backend\Action\Schema\Create::class, Backend\Model\Schema_Create::class, [200 => Message::class], null, 'backend.schema'),
                ],
                '/schema/preview/$schema_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Schema\GetPreview::class, null, [200 => Backend\Model\Schema_Preview_Response::class], null, 'backend.schema'),
                ],
                '/schema/form/$schema_id<[0-9]+>' => [
                    'POST' => new Method(Backend\Action\Schema\Form::class, Backend\Model\Schema_Form::class, [200 => Message::class], null, 'backend.schema'),
                ],
                '/schema/$schema_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Schema\Get::class, null, [200 => Backend\Model\Schema::class], null, 'backend.schema'),
                    'PUT' => new Method(Backend\Action\Schema\Update::class, Backend\Model\Schema_Update::class, [200 => Message::class], null, 'backend.schema'),
                    'DELETE' => new Method(Backend\Action\Schema\Delete::class, null, [200 => Message::class], null, 'backend.schema'),
                ],
                '/scope' => [
                    'GET' => new Method(Backend\Action\Scope\GetAll::class, null, [200 => Backend\Model\Scope_Collection::class], Collection_Category_Query::class, 'backend.scope'),
                    'POST' => new Method(Backend\Action\Scope\Create::class, Backend\Model\Scope_Create::class, [200 => Message::class], null, 'backend.scope'),
                ],
                '/scope/$scope_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Scope\Get::class, null, [200 => Backend\Model\Scope::class], null, 'backend.scope'),
                    'PUT' => new Method(Backend\Action\Scope\Update::class, Backend\Model\Scope_Update::class, [200 => Message::class], null, 'backend.scope'),
                    'DELETE' => new Method(Backend\Action\Scope\Delete::class, null, [200 => Message::class], null, 'backend.scope'),
                ],
                '/sdk' => [
                    'GET' => new Method(Backend\Action\Sdk\GetAll::class, null, [200 => Backend\Model\Sdk_Types::class], null, 'backend.sdk'),
                    'POST' => new Method(Backend\Action\Sdk\Generate::class, Backend\Model\Sdk_Generate::class, [200 => Message::class], null, 'backend.sdk'),
                ],
                '/statistic/count_requests' => [
                    'GET' => new Method(Backend\Action\Statistic\GetCountRequests::class, null, [200 => Backend\Model\Statistic_Count::class], Backend\Model\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/errors_per_route' => [
                    'GET' => new Method(Backend\Action\Statistic\GetErrorsPerRoute::class, null, [200 => Backend\Model\Statistic_Count::class], Backend\Model\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/incoming_requests' => [
                    'GET' => new Method(Backend\Action\Statistic\GetIncomingRequests::class, null, [200 => Backend\Model\Statistic_Count::class], Backend\Model\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/incoming_transactions' => [
                    'GET' => new Method(Backend\Action\Statistic\GetIncomingTransactions::class, null, [200 => Backend\Model\Statistic_Count::class], Backend\Model\Transaction_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/issued_tokens' => [
                    'GET' => new Method(Backend\Action\Statistic\GetIssuedTokens::class, null, [200 => Backend\Model\Statistic_Count::class], Backend\Model\App_Token_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/most_used_apps' => [
                    'GET' => new Method(Backend\Action\Statistic\GetMostUsedApps::class, null, [200 => Backend\Model\Statistic_Count::class], Backend\Model\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/most_used_routes' => [
                    'GET' => new Method(Backend\Action\Statistic\GetMostUsedRoutes::class, null, [200 => Backend\Model\Statistic_Count::class], Backend\Model\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/time_average' => [
                    'GET' => new Method(Backend\Action\Statistic\GetTimeAverage::class, null, [200 => Backend\Model\Statistic_Count::class], Backend\Model\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/time_per_route' => [
                    'GET' => new Method(Backend\Action\Statistic\GetTimePerRoute::class, null, [200 => Backend\Model\Statistic_Count::class], Backend\Model\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/used_points' => [
                    'GET' => new Method(Backend\Action\Statistic\GetUsedPoints::class, null, [200 => Backend\Model\Statistic_Count::class], Backend\Model\Plan_Usage_Collection_Query::class, 'backend.statistic'),
                ],
                '/transaction' => [
                    'GET' => new Method(Backend\Action\Transaction\GetAll::class, null, [200 => Backend\Model\Transaction_Collection::class], Backend\Model\Transaction_Collection_Query::class, 'backend.transaction'),
                ],
                '/transaction/$transaction_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Transaction\Get::class, null, [200 => Backend\Model\Transaction::class], null, 'backend.transaction'),
                ],
                '/user' => [
                    'GET' => new Method(Backend\Action\User\GetAll::class, null, [200 => Backend\Model\User_Collection::class], Collection_Query::class, 'backend.user'),
                    'POST' => new Method(Backend\Action\User\Create::class, Backend\Model\User_Create::class, [200 => Message::class], null, 'backend.user'),
                ],
                '/user/$user_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\User\Get::class, null, [200 => Backend\Model\User::class], null, 'backend.user'),
                    'PUT' => new Method(Backend\Action\User\Update::class, Backend\Model\User_Update::class, [200 => Message::class], null, 'backend.user'),
                    'DELETE' => new Method(Backend\Action\User\Delete::class, null, [200 => Message::class], null, 'backend.user'),
                ],
            ],
            'consumer' => [
                '/app' => [
                    'GET' => new Method(Consumer\Action\App\GetAll::class, null, [200 => Consumer\Model\App_Collection::class], Collection_Query::class, 'consumer.app'),
                    'POST' => new Method(Consumer\Action\App\Create::class, Consumer\Model\App_Create::class, [200 => Message::class], null, 'consumer.app'),
                ],
                '/app/$app_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\App\Get::class, null, [200 => Consumer\Model\App::class], null, 'consumer.app'),
                    'PUT' => new Method(Consumer\Action\App\Update::class, Consumer\Model\App_Update::class, [200 => Message::class], null, 'consumer.app'),
                    'DELETE' => new Method(Consumer\Action\App\Delete::class, null, [200 => Message::class], null, 'consumer.app'),
                ],
                '/event' => [
                    'GET' => new Method(Consumer\Action\Event\GetAll::class, null, [200 => Consumer\Model\Event_Collection::class], Collection_Query::class, 'consumer.event'),
                ],
                '/grant' => [
                    'GET' => new Method(Consumer\Action\Grant\GetAll::class, null, [200 => Consumer\Model\Grant_Collection::class], Collection_Query::class, 'consumer.grant'),
                ],
                '/grant/$grant_id<[0-9]+>' => [
                    'DELETE' => new Method(Consumer\Action\Grant\Delete::class, null, [200 => Message::class], null, 'consumer.grant'),
                ],
                '/plan/contract' => [
                    'GET' => new Method(Consumer\Action\Plan\Contract\GetAll::class, null, [200 => Consumer\Model\Plan_Contract_Collection::class], Collection_Query::class, 'consumer.plan'),
                    'POST' => new Method(Consumer\Action\Plan\Contract\Create::class, null, [200 => Consumer\Model\Plan_Order_Request::class], null, 'consumer.plan'),
                ],
                '/plan/contract/$contract_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Plan\Contract\Get::class, null, [200 => Consumer\Model\Plan_Contract::class], null, 'consumer.plan'),
                ],
                '/plan/invoice' => [
                    'GET' => new Method(Consumer\Action\Plan\Invoice\GetAll::class, null, [200 => Consumer\Model\Plan_Invoice_Collection::class], Collection_Query::class, 'consumer.plan'),
                ],
                '/plan/invoice/$invoice_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Plan\Invoice\Get::class, null, [200 => Consumer\Model\Plan_Invoice::class], null, 'consumer.plan'),
                ],
                '/plan' => [
                    'GET' => new Method(Consumer\Action\Plan\GetAll::class, null, [200 => Consumer\Model\Plan_Collection::class], Collection_Query::class, 'consumer.plan'),
                ],
                '/plan/$plan_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Plan\Get::class, null, [200 => Consumer\Model\Plan::class], null, 'consumer.plan'),
                ],
                '/scope' => [
                    'GET' => new Method(Consumer\Action\Scope\GetAll::class, null, [200 => Consumer\Model\Scope_Collection::class], Collection_Query::class, 'consumer.scope'),
                ],
                '/subscription' => [
                    'GET' => new Method(Consumer\Action\Event\Subscription\GetAll::class, null, [200 => Consumer\Model\Event_Subscription_Collection::class], Collection_Query::class, 'consumer.event'),
                    'POST' => new Method(Consumer\Action\Event\Subscription\Create::class, Consumer\Model\Event_Subscription_Create::class, [200 => Message::class], null, 'consumer.event'),
                ],
                '/subscription/$subscription_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Event\Subscription\Get::class, null, [200 => Consumer\Model\Event_Subscription::class], null, 'consumer.event'),
                    'PUT' => new Method(Consumer\Action\Event\Subscription\Update::class, Consumer\Model\Event_Subscription_Update::class, [200 => Message::class], null, 'consumer.event'),
                    'DELETE' => new Method(Consumer\Action\Event\Subscription\Delete::class, null, [200 => Message::class], null, 'consumer.event'),
                ],
                '/transaction' => [
                    'GET' => new Method(Consumer\Action\Transaction\GetAll::class, null, [200 => Consumer\Model\Transaction_Collection::class], Collection_Query::class, 'consumer.transaction'),
                ],
                '/transaction/execute/:transaction_id' => [
                    'POST' => new Method(Consumer\Action\Transaction\Execute::class, null, [], null, 'consumer.transaction'),
                ],
                '/transaction/prepare/:provider' => [
                    'POST' => new Method(Consumer\Action\Transaction\Execute::class, Consumer\Model\Transaction_Prepare_Request::class, [200 => Consumer\Model\Transaction_Prepare_Response::class], null, 'consumer.transaction'),
                ],
                '/transaction/$transaction_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Transaction\Get::class, null, [200 => Consumer\Model\Transaction::class], null, 'consumer.transaction'),
                ],
                '/account' => [
                    'GET' => new Method(Consumer\Action\User\Get::class, null, [200 => Consumer\Model\User_Account::class], null, 'consumer.user'),
                ],
                '/account/change_password' => [
                    'PUT' => new Method(Consumer\Action\User\ChangePassword::class, null, [200 => Backend\Model\Account_ChangePassword::class], null, 'consumer.user'),
                ],
                '/activate' => [
                    'POST' => new Method(Consumer\Action\User\Activate::class, Consumer\Model\User_Activate::class, [200 => Message::class], null, 'consumer.user'),
                ],
                '/authorize' => [
                    'GET' => new Method(Consumer\Action\User\GetApp::class, null, [200 => Consumer\Model\Authorize_Meta::class], null, 'consumer.user'),
                    'POST' => new Method(Consumer\Action\User\Authorize::class, Consumer\Model\Authorize_Request::class, [200 => Consumer\Model\Authorize_Response::class], null, 'consumer.user'),
                ],
                '/login' => [
                    'POST' => new Method(Consumer\Action\User\Login::class, Consumer\Model\User_Login::class, [200 => Consumer\Model\User_JWT::class], null, 'consumer.user'),
                    'PUT' => new Method(Consumer\Action\User\Refresh::class, Consumer\Model\User_Refresh::class, [200 => Consumer\Model\User_JWT::class], null, 'consumer.user'),
                ],
                '/provider/:provider' => [
                    'POST' => new Method(Consumer\Action\User\Provider::class, Consumer\Model\User_Provider::class, [200 => Consumer\Model\User_JWT::class], null, 'consumer.user'),
                ],
                '/register' => [
                    'POST' => new Method(Consumer\Action\User\Register::class, Consumer\Model\User_Register::class, [200 => Message::class], null, 'consumer.user'),
                ],
                '/password_reset' => [
                    'POST' => new Method(Consumer\Action\User\ResetPassword\Request::class, Consumer\Model\User_Email::class, [200 => Message::class], null, 'consumer.user'),
                    'PUT' => new Method(Consumer\Action\User\ResetPassword\Execute::class, Consumer\Model\User_PasswordReset::class, [200 => Message::class], null, 'consumer.user'),
                ],
            ],
            'system' => [
                '/route' => [
                    'GET' => new Method(System\Action\GetAllRoute::class, null, [200 => System\Model\Route::class]),
                ],
                '/invoke/:method' => [
                    'POST' => new Method(System\Action\Invoke::class, Passthru::class, [200 => Passthru::class]),
                ],
                '/health' => [
                    'GET' => new Method(System\Action\GetHealth::class, null, [200 => System\Model\Health_Check::class]),
                ],
                '/debug' => [
                    'GET' => new Method(System\Action\GetDebug::class, null, [200 => System\Model\Debug::class]),
                    'POST' => new Method(System\Action\GetDebug::class, Passthru::class, [200 => System\Model\Debug::class]),
                    'PUT' => new Method(System\Action\GetDebug::class, Passthru::class, [200 => System\Model\Debug::class]),
                    'DELETE' => new Method(System\Action\GetDebug::class, null, [200 => System\Model\Debug::class]),
                    'PATCH' => new Method(System\Action\GetDebug::class, Passthru::class, [200 => System\Model\Debug::class]),
                ],
                '/schema/:name' => [
                    'GET' => new Method(System\Action\GetSchema::class, null, [200 => System\Model\Schema::class]),
                ],
            ],
            'authorization' => [
                '/revoke' => [
                    'POST' => new Method(Authorization\Action\Revoke::class, null, [200 => Message::class]),
                ],
                '/whoami' => [
                    'GET' => new Method(Authorization\Action\GetWhoami::class, null, [200 => Backend\Model\User::class]),
                ],
            ],
        ];

        self::$data = [
            'fusio_user' => [
                ['status' => 1, 'name' => 'Administrator', 'email' => 'admin@localhost.com', 'password' => $password, 'points' => null, 'date' => $now->format('Y-m-d H:i:s')],
            ],
            'fusio_action' => [
            ],
            'fusio_app' => [
                ['user_id' => 1, 'status' => 1, 'name' => 'Backend',  'url' => 'http://fusio-project.org', 'parameters' => '', 'app_key' => $backendAppKey, 'app_secret' => $backendAppSecret, 'date' => $now->format('Y-m-d H:i:s')],
                ['user_id' => 1, 'status' => 1, 'name' => 'Consumer', 'url' => 'http://fusio-project.org', 'parameters' => '', 'app_key' => $consumerAppKey, 'app_secret' => $consumerAppSecret, 'date' => $now->format('Y-m-d H:i:s')],
            ],
            'fusio_audit' => [
            ],
            'fusio_config' => [
                ['name' => 'app_approval', 'type' => Table\Config::FORM_BOOLEAN, 'description' => 'If true the status of a new app is PENDING so that an administrator has to manually activate the app', 'value' => 0],
                ['name' => 'app_consumer', 'type' => Table\Config::FORM_NUMBER, 'description' => 'The max amount of apps a consumer can register', 'value' => 16],

                ['name' => 'authorization_url', 'type' => Table\Config::FORM_STRING, 'description' => 'Url where the user can authorize for the OAuth2 flow', 'value' => ''],

                ['name' => 'consumer_subscription', 'type' => Table\Config::FORM_NUMBER, 'description' => 'The max amount of subscriptions a consumer can add', 'value' => 8],

                ['name' => 'info_title', 'type' => Table\Config::FORM_STRING, 'description' => 'The title of the application', 'value' => 'Fusio'],
                ['name' => 'info_description', 'type' => Table\Config::FORM_STRING, 'description' => 'A short description of the application. CommonMark syntax MAY be used for rich text representation', 'value' => ''],
                ['name' => 'info_tos', 'type' => Table\Config::FORM_STRING, 'description' => 'A URL to the Terms of Service for the API. MUST be in the format of a URL', 'value' => ''],
                ['name' => 'info_contact_name', 'type' => Table\Config::FORM_STRING, 'description' => 'The identifying name of the contact person/organization', 'value' => ''],
                ['name' => 'info_contact_url', 'type' => Table\Config::FORM_STRING, 'description' => 'The URL pointing to the contact information. MUST be in the format of a URL', 'value' => ''],
                ['name' => 'info_contact_email', 'type' => Table\Config::FORM_STRING, 'description' => 'The email address of the contact person/organization. MUST be in the format of an email address', 'value' => ''],
                ['name' => 'info_license_name', 'type' => Table\Config::FORM_STRING, 'description' => 'The license name used for the API', 'value' => ''],
                ['name' => 'info_license_url', 'type' => Table\Config::FORM_STRING, 'description' => 'A URL to the license used for the API. MUST be in the format of a URL', 'value' => ''],

                ['name' => 'mail_register_subject', 'type' => Table\Config::FORM_STRING, 'description' => 'Subject of the activation mail', 'value' => 'Fusio registration'],
                ['name' => 'mail_register_body', 'type' => Table\Config::FORM_TEXT, 'description' => 'Body of the activation mail', 'value' => 'Hello {name},' . "\n\n" . 'you have successful registered at Fusio.' . "\n" . 'To activate you account please visit the following link:' . "\n" . 'http://127.0.0.1/projects/fusio/public/consumer/#activate?token={token}'],
                ['name' => 'mail_pw_reset_subject', 'type' => Table\Config::FORM_STRING, 'description' => 'Subject of the password reset mail', 'value' => 'Fusio password reset'],
                ['name' => 'mail_pw_reset_body', 'type' => Table\Config::FORM_TEXT, 'description' => 'Body of the password reset mail', 'value' => 'Hello {name},' . "\n\n" . 'you have requested to reset your password.' . "\n" . 'To set a new password please visit the following link:' . "\n" . 'http://127.0.0.1/projects/fusio/public/consumer/#password_reset?token={token}' . "\n\n" . 'Please ignore this email if you have not requested a password reset.'],
                ['name' => 'mail_sender', 'type' => Table\Config::FORM_STRING, 'description' => 'Email address which is used in the "From" header', 'value' => ''],

                ['name' => 'provider_facebook_secret', 'type' => Table\Config::FORM_STRING, 'description' => 'Facebook app secret', 'value' => ''],
                ['name' => 'provider_google_secret', 'type' => Table\Config::FORM_STRING, 'description' => 'Google app secret', 'value' => ''],
                ['name' => 'provider_github_secret', 'type' => Table\Config::FORM_STRING, 'description' => 'GitHub app secret', 'value' => ''],

                ['name' => 'recaptcha_secret', 'type' => Table\Config::FORM_STRING, 'description' => 'ReCaptcha secret', 'value' => ''],

                ['name' => 'scopes_default', 'type' => Table\Config::FORM_STRING, 'description' => 'If a user registers through the consumer API the following scopes are assigned', 'value' => 'authorization,consumer'],
                ['name' => 'points_default', 'type' => Table\Config::FORM_NUMBER, 'description' => 'The default amount of points which a user receives if he registers', 'value' => 0],

                ['name' => 'system_mailer', 'type' => Table\Config::FORM_STRING, 'description' => 'Optional a SMTP connection which is used as mailer', 'value' => ''],
                ['name' => 'system_dispatcher', 'type' => Table\Config::FORM_STRING, 'description' => 'Optional a HTTP or message queue connection which is used to dispatch events', 'value' => ''],

                ['name' => 'user_pw_length', 'type' => Table\Config::FORM_NUMBER, 'description' => 'Minimal required password length', 'value' => 8],
                ['name' => 'user_approval', 'type' => Table\Config::FORM_BOOLEAN, 'description' => 'Whether the user needs to activate the account through an email', 'value' => 1],
            ],
            'fusio_category' => [
            ],
            'fusio_connection' => [
                ['status' => 1, 'name' => 'System', 'class' => ConnectionSystem::class, 'config' => null],
            ],
            'fusio_cronjob' => [
            ],
            'fusio_event' => [
            ],
            'fusio_log' => [
            ],
            'fusio_plan' => [
            ],
            'fusio_plan_contract' => [
            ],
            'fusio_plan_invoice' => [
            ],
            'fusio_provider' => [
            ],
            'fusio_rate' => [
                ['status' => 1, 'priority' => 0, 'name' => 'Default', 'rate_limit' => 720, 'timespan' => 'PT1H'],
                ['status' => 1, 'priority' => 4, 'name' => 'Default-Anonymous', 'rate_limit' => 60, 'timespan' => 'PT1H'],
            ],
            'fusio_routes' => [
                ['category_id' => 2, 'status' => 1, 'priority' => 0, 'methods' => 'ANY', 'path' => '/backend/token',             'controller' => Backend\Authorization\Token::class],
                ['category_id' => 3, 'status' => 1, 'priority' => 0, 'methods' => 'ANY', 'path' => '/consumer/token',            'controller' => Consumer\Authorization\Token::class],
                ['category_id' => 4, 'status' => 1, 'priority' => 1, 'methods' => 'ANY', 'path' => '/system/jsonrpc',            'controller' => System\Api\JsonRpc::class],
                ['category_id' => 4, 'status' => 1, 'priority' => 2, 'methods' => 'GET', 'path' => '/system/doc',                'controller' => Tool\Documentation\IndexController::class],
                ['category_id' => 4, 'status' => 1, 'priority' => 3, 'methods' => 'GET', 'path' => '/system/doc/:version/*path', 'controller' => Tool\Documentation\DetailController::class],
                ['category_id' => 4, 'status' => 1, 'priority' => 4, 'methods' => 'GET', 'path' => '/system/export/:type/:version/*path', 'controller' => Generator\GeneratorController::class],
                ['category_id' => 5, 'status' => 1, 'priority' => 1, 'methods' => 'ANY', 'path' => '/authorization/token',       'controller' => Authorization\Token::class],
            ],
            'fusio_schema' => [
                ['status' => 1, 'name' => 'Passthru', 'source' => Passthru::class, 'form' => null]
            ],
            'fusio_scope' => [
            ],
            'fusio_transaction' => [
            ],
            'fusio_app_code' => [
            ],
            'fusio_app_scope' => [
                ['app_id' => 1, 'scope_id' => 1],
                ['app_id' => 1, 'scope_id' => 3],
                ['app_id' => 2, 'scope_id' => 2],
                ['app_id' => 2, 'scope_id' => 3],
            ],
            'fusio_app_token' => [
            ],
            'fusio_cronjob_error' => [
            ],
            'fusio_event_subscription' => [
            ],
            'fusio_event_trigger' => [
            ],
            'fusio_event_response' => [
            ],
            'fusio_log_error' => [
            ],
            'fusio_plan_usage' => [
            ],
            'fusio_rate_allocation' => [
                ['rate_id' => 1, 'route_id' => null, 'app_id' => null, 'authenticated' => null, 'parameters' => null],
                ['rate_id' => 2, 'route_id' => null, 'app_id' => null, 'authenticated' => 0, 'parameters' => null],
            ],
            'fusio_routes_method' => [
            ],
            'fusio_routes_response' => [
            ],
            'fusio_scope_routes' => [
            ],
            'fusio_user_grant' => [
            ],
            'fusio_user_scope' => [
                ['user_id' => 1, 'scope_id' => 1],
                ['user_id' => 1, 'scope_id' => 2],
                ['user_id' => 1, 'scope_id' => 3],
            ],
            'fusio_user_attribute' => [
            ],
        ];

        $categoryId = 1;
        $prio = 0;

        foreach ($config as $category => $routes) {
            self::$data['fusio_category'][] = [
                'name' => $category,
            ];

            self::addScope($categoryId, $category);

            foreach ($routes as $route => $config) {
                $path = '/' . $category . $route;
                self::addRoute($categoryId, $prio, $path);

                foreach ($config as $methodName => $method) {
                    $actionName = self::getActionName($method->getAction());
                    self::addAction($categoryId, $actionName, $method->getAction());

                    $requestName = null;
                    if ($method->getRequest()) {
                        $requestName = self::getSchemaName($method->getRequest());
                        self::addSchema($categoryId, $requestName, $method->getRequest());
                    }

                    if ($method->getScope()) {
                        self::addScope($categoryId, $method->getScope());
                        self::addScopeRoute($method->getScope(), $path);
                    }

                    if ($method->getEventName()) {
                        self::addEvent($categoryId, $method->getEventName());
                    }

                    self::addRouteMethod($path, $methodName, $requestName, $actionName);

                    foreach ($method->getResponses() as $code => $response) {
                        $responseName = self::getSchemaName($response);

                        self::addSchema($categoryId, $responseName, $response);
                        self::addRouteMethodResponse($path, $methodName, $code, $responseName);
                    }
                }

                $prio++;
            }

            $categoryId++;
        }

        $result = [];
        foreach (self::$data as $key => $value) {
            $result[$key] = array_values($value);
        }

        return self::$data = $result;
    }

    private static function getActionName(string $class): string
    {
        $parts = explode('\\', $class);
        array_shift($parts);
        array_shift($parts);
        return implode('_', $parts);
    }

    private static function getSchemaName(string $class): string
    {
        $parts = explode('\\', $class);
        array_shift($parts);
        array_shift($parts);
        return implode('_', $parts);
    }

    private static function addRoute(int $categoryId, int $prio, string $path)
    {
        self::$data['fusio_routes'][$path] = [
            'category_id' => $categoryId,
            'status' => 1,
            'priority' => $prio,
            'methods' => 'ANY',
            'path' => $path,
            'controller' => SchemaApiController::class
        ];
    }

    private static function addRouteMethod(string $path, string $methodName, ?string $request, string $action)
    {
        self::$data['fusio_routes_method'][$path . $methodName] = [
            'route_id' => self::getId('fusio_routes', $path),
            'method' => $methodName,
            'version' => 1,
            'status' => Resource::STATUS_ACTIVE,
            'active' => 1,
            'public' => 0,
            'parameters' => null,
            'request' => $request,
            'action' => $action,
            'costs' => null
        ];
    }

    private static function addRouteMethodResponse(string $path, string $methodName, string $code, string $response)
    {
        self::$data['fusio_routes_response'][$path . $methodName . $code] = [
            'method_id' => self::getId('fusio_routes_method', $path . $methodName),
            'code' => $code,
            'response' => $response
        ];
    }

    private static function addAction(int $categoryId, string $name, string $class)
    {
        self::$data['fusio_action'][$name] = [
            'category_id' => $categoryId,
            'status' => 1,
            'name' => $name,
            'class' => $class,
            'engine' => PhpClass::class,
            'config' => null,
            'date' => (new \DateTime())->format('Y-m-d H:i:s')
        ];
    }

    private static function addSchema(int $categoryId, string $name, string $class)
    {
        self::$data['fusio_schema'][$name] = [
            'category_id' => $categoryId,
            'status' => 1,
            'name' => $name,
            'source' => $class,
            'form' => null
        ];
    }

    private static function addEvent(int $categoryId, string $name)
    {
        self::$data['fusio_event'][$name] = [
            'category_id' => $categoryId,
            'status' => Table\Event::STATUS_ACTIVE,
            'name' => $name,
            'description' => ''
        ];
    }

    private static function addScope(int $categoryId, string $name)
    {
        self::$data['fusio_scope'][$name] = [
            'category_id' => $categoryId,
            'name' => $name,
            'description' => ''
        ];
    }

    private static function addScopeRoute(string $scope, string $path)
    {
        self::$data['fusio_scope_routes'][$scope . $path] = [
            'scope_id' => self::getId('fusio_scope', $scope),
            'route_id' => self::getId('fusio_routes', $path),
            'allow' => 1,
            'methods' => 'GET|POST|PUT|PATCH|DELETE'
        ];
    }

    private static function getId(string $type, string $name): ?int
    {
        $index = 1;
        foreach (self::$data[$type] as $key => $value) {
            if ($name === $key) {
                return $index;
            }
            $index++;
        }

        return null;
    }
}

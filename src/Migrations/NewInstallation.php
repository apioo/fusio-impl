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
use Fusio\Impl\Action\Welcome;
use Fusio\Impl\Authorization;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Backend;
use Fusio\Impl\Connection\System as ConnectionSystem;
use Fusio\Impl\Consumer;
use Fusio\Impl\Model\Collection_Category_Query;
use Fusio\Impl\Model\Collection_Query;
use Fusio\Impl\Model\Form_Container;
use Fusio\Impl\Model\Message;
use Fusio\Impl\System;
use Fusio\Impl\Table;
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

    public static function getData(): DataBag
    {
        if (self::$data) {
            return self::$data;
        }

        $backendAppKey     = TokenGenerator::generateAppKey();
        $backendAppSecret  = TokenGenerator::generateAppSecret();
        $consumerAppKey    = TokenGenerator::generateAppKey();
        $consumerAppSecret = TokenGenerator::generateAppSecret();
        $password          = \password_hash(TokenGenerator::generateUserPassword(), PASSWORD_DEFAULT);

        $bag = new DataBag();
        $bag->addCategory('default');
        $bag->addCategory('backend');
        $bag->addCategory('consumer');
        $bag->addCategory('system');
        $bag->addCategory('authorization');
        $bag->addUser('Administrator', 'admin@localhost.com', $password);
        $bag->addApp('Administrator', 'Backend', 'http://fusio-project.org', $backendAppKey, $backendAppSecret);
        $bag->addApp('Administrator', 'Consumer', 'http://fusio-project.org', $consumerAppKey, $consumerAppSecret);
        $bag->addScope('backend', 'backend', 'Global access to the backend API');
        $bag->addScope('consumer', 'consumer', 'Global access to the consumer API');
        $bag->addScope('authorization', 'authorization', 'Authorization API endpoint');
        $bag->addAppScope('Backend', 'backend');
        $bag->addAppScope('Backend', 'authorization');
        $bag->addAppScope('Consumer', 'consumer');
        $bag->addAppScope('Consumer', 'authorization');
        $bag->addConfig('app_approval', Table\Config::FORM_BOOLEAN, 0, 'If true the status of a new app is PENDING so that an administrator has to manually activate the app');
        $bag->addConfig('app_consumer', Table\Config::FORM_NUMBER, 16, 'The max amount of apps a consumer can register');
        $bag->addConfig('authorization_url', Table\Config::FORM_STRING, '', 'Url where the user can authorize for the OAuth2 flow');
        $bag->addConfig('consumer_subscription', Table\Config::FORM_NUMBER, 8, 'The max amount of subscriptions a consumer can add');
        $bag->addConfig('info_title', Table\Config::FORM_STRING, 'Fusio', 'The title of the application');
        $bag->addConfig('info_description', Table\Config::FORM_STRING, '', 'A short description of the application. CommonMark syntax MAY be used for rich text representation');
        $bag->addConfig('info_tos', Table\Config::FORM_STRING, '', 'A URL to the Terms of Service for the API. MUST be in the format of a URL');
        $bag->addConfig('info_contact_name', Table\Config::FORM_STRING, '', 'The identifying name of the contact person/organization');
        $bag->addConfig('info_contact_url', Table\Config::FORM_STRING, '', 'The URL pointing to the contact information. MUST be in the format of a URL');
        $bag->addConfig('info_contact_email', Table\Config::FORM_STRING, '', 'The email address of the contact person/organization. MUST be in the format of an email address');
        $bag->addConfig('info_license_name', Table\Config::FORM_STRING, '', 'The license name used for the API');
        $bag->addConfig('info_license_url', Table\Config::FORM_STRING, '', 'A URL to the license used for the API. MUST be in the format of a URL');
        $bag->addConfig('mail_register_subject', Table\Config::FORM_STRING, 'Fusio registration', 'Subject of the activation mail');
        $bag->addConfig('mail_register_body', Table\Config::FORM_TEXT, 'Hello {name},' . "\n\n" . 'you have successful registered at Fusio.' . "\n" . 'To activate you account please visit the following link:' . "\n" . 'http://127.0.0.1/projects/fusio/public/consumer/#activate?token={token}', 'Body of the activation mail');
        $bag->addConfig('mail_pw_reset_subject', Table\Config::FORM_STRING, 'Fusio password reset', 'Subject of the password reset mail');
        $bag->addConfig('mail_pw_reset_body', Table\Config::FORM_TEXT, 'Hello {name},' . "\n\n" . 'you have requested to reset your password.' . "\n" . 'To set a new password please visit the following link:' . "\n" . 'http://127.0.0.1/projects/fusio/public/consumer/#password_reset?token={token}' . "\n\n" . 'Please ignore this email if you have not requested a password reset.', 'Body of the password reset mail');
        $bag->addConfig('mail_sender', Table\Config::FORM_STRING, '', 'Email address which is used in the "From" header');
        $bag->addConfig('provider_facebook_secret', Table\Config::FORM_STRING, '', 'Facebook app secret');
        $bag->addConfig('provider_google_secret', Table\Config::FORM_STRING, '', 'Google app secret');
        $bag->addConfig('provider_github_secret', Table\Config::FORM_STRING, '', 'GitHub app secret');
        $bag->addConfig('recaptcha_secret', Table\Config::FORM_STRING, '', 'ReCaptcha secret');
        $bag->addConfig('scopes_default', Table\Config::FORM_STRING, 'authorization,consumer', 'If a user registers through the consumer API the following scopes are assigned');
        $bag->addConfig('points_default', Table\Config::FORM_NUMBER, 0, 'The default amount of points which a user receives if he registers');
        $bag->addConfig('system_mailer', Table\Config::FORM_STRING, '', 'Optional a SMTP connection which is used as mailer');
        $bag->addConfig('system_dispatcher', Table\Config::FORM_STRING, '', 'Optional a HTTP or message queue connection which is used to dispatch events');
        $bag->addConfig('user_pw_length', Table\Config::FORM_NUMBER, 8, 'Minimal required password length');
        $bag->addConfig('user_approval', Table\Config::FORM_BOOLEAN, 1, 'Whether the user needs to activate the account through an email');
        $bag->addConnection('System', ConnectionSystem::class);
        $bag->addRate('Default', 0, 720, 'PT1H');
        $bag->addRate('Default-Anonymous', 4, 60, 'PT1H');
        $bag->addRateAllocation('Default');
        $bag->addRateAllocation('Default-Anonymous', null, null, 0);
        $bag->addRoute('backend', 0, '/backend/token', Backend\Authorization\Token::class);
        $bag->addRoute('consumer', 0, '/consumer/token', Consumer\Authorization\Token::class);
        $bag->addRoute('system', 0, '/system/jsonrpc', System\Api\JsonRpc::class);
        $bag->addRoute('system', 1, '/system/doc', Tool\Documentation\IndexController::class);
        $bag->addRoute('system', 2, '/system/doc/:version/*path', Tool\Documentation\DetailController::class);
        $bag->addRoute('system', 30, '/system/export/:type/:version/*path', Generator\GeneratorController::class);
        $bag->addRoute('authorization', 0, '/authorization/token', Authorization\Token::class);
        $bag->addSchema('default', 'Passthru', Passthru::class);
        $bag->addUserScope('Administrator', 'backend');
        $bag->addUserScope('Administrator', 'consumer');
        $bag->addUserScope('Administrator', 'authorization');

        foreach (self::getRoutes() as $category => $routes) {
            $bag->addRoutes($category, $routes);
        }

        return self::$data = $bag;
    }

    private static function getRoutes(): array
    {
        return [
            'default' => [
                '/' => [
                    'GET' => new Method(Welcome::class, null, [200 => 'Passthru']),
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
                    'POST' => new Method(Backend\Action\Action\Create::class, Backend\Model\Action_Create::class, [201 => Message::class], null, 'backend.action', 'fusio.action.create'),
                ],
                '/action/list' => [
                    'GET' => new Method(Backend\Action\Action\GetIndex::class, null, [200 => Backend\Model\Action_Index::class], null, 'backend.action'),
                ],
                '/action/form' => [
                    'GET' => new Method(Backend\Action\Action\GetForm::class, null, [200 => Form_Container::class], null, 'backend.action'),
                ],
                '/action/execute/:action_id' => [
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
                    'POST' => new Method(Backend\Action\App\Create::class, Backend\Model\App_Create::class, [201 => Message::class], null, 'backend.app', 'fusio.app.create'),
                ],
                '/app/$app_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\App\Get::class, null, [200 => Backend\Model\App::class], null, 'backend.app'),
                    'PUT' => new Method(Backend\Action\App\Update::class, Backend\Model\App_Update::class, [200 => Message::class], null, 'backend.app', 'fusio.app.update'),
                    'DELETE' => new Method(Backend\Action\App\Delete::class, null, [200 => Message::class], null, 'backend.app', 'fusio.app.delete'),
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
                    'POST' => new Method(Backend\Action\Connection\Create::class, Backend\Model\Connection_Create::class, [201 => Message::class], null, 'backend.connection', 'fusio.connection.create'),
                ],
                '/connection/list' => [
                    'GET' => new Method(Backend\Action\Connection\GetIndex::class, null, [200 => Backend\Model\Connection_Index::class], null, 'backend.connection'),
                ],
                '/connection/form' => [
                    'GET' => new Method(Backend\Action\Connection\GetForm::class, null, [200 => Form_Container::class], null, 'backend.connection'),
                ],
                '/connection/$connection_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Connection\Get::class, null, [200 => Backend\Model\Connection::class], null, 'backend.connection'),
                    'PUT' => new Method(Backend\Action\Connection\Update::class, Backend\Model\Connection_Update::class, [200 => Message::class], null, 'backend.connection', 'fusio.connection.update'),
                    'DELETE' => new Method(Backend\Action\Connection\Delete::class, null, [200 => Message::class], null, 'backend.connection', 'fusio.connection.delete'),
                ],
                '/cronjob' => [
                    'GET' => new Method(Backend\Action\Cronjob\GetAll::class, null, [200 => Backend\Model\Cronjob_Collection::class], Collection_Category_Query::class, 'backend.cronjob'),
                    'POST' => new Method(Backend\Action\Cronjob\Create::class, Backend\Model\Cronjob_Create::class, [201 => Message::class], null, 'backend.cronjob', 'fusio.cronjob.create'),
                ],
                '/cronjob/$cronjob_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Cronjob\Get::class, null, [200 => Backend\Model\Cronjob::class], null, 'backend.cronjob'),
                    'PUT' => new Method(Backend\Action\Cronjob\Update::class, Backend\Model\Cronjob_Update::class, [200 => Message::class], null, 'backend.cronjob', 'fusio.cronjob.update'),
                    'DELETE' => new Method(Backend\Action\Cronjob\Delete::class, null, [200 => Message::class], null, 'backend.cronjob', 'fusio.cronjob.delete'),
                ],
                '/dashboard' => [
                    'GET' => new Method(Backend\Action\Dashboard\GetAll::class, null, [200 => Backend\Model\Dashboard::class], null, 'backend.dashboard'),
                ],
                '/event/subscription' => [
                    'GET' => new Method(Backend\Action\Event\Subscription\GetAll::class, null, [200 => Backend\Model\Event_Subscription_Collection::class], Collection_Query::class, 'backend.event'),
                    'POST' => new Method(Backend\Action\Event\Subscription\Create::class, Backend\Model\Event_Subscription_Create::class, [201 => Message::class], null, 'backend.event', 'fusio.event.subscription.create'),
                ],
                '/event/subscription/$subscription_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Event\Subscription\Get::class, null, [200 => Backend\Model\Event_Subscription::class], null, 'backend.event'),
                    'PUT' => new Method(Backend\Action\Event\Subscription\Update::class, Backend\Model\Event_Subscription_Update::class, [200 => Message::class], null, 'backend.event', 'fusio.event.subscription.update'),
                    'DELETE' => new Method(Backend\Action\Event\Subscription\Delete::class, null, [200 => Message::class], null, 'backend.event', 'fusio.event.subscription.delete'),
                ],
                '/event' => [
                    'GET' => new Method(Backend\Action\Event\GetAll::class, null, [200 => Backend\Model\Event_Collection::class], Collection_Category_Query::class, 'backend.event'),
                    'POST' => new Method(Backend\Action\Event\Create::class, Backend\Model\Event_Create::class, [201 => Message::class], null, 'backend.event', 'fusio.event.create'),
                ],
                '/event/$event_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Event\Get::class, null, [200 => Backend\Model\Event::class], null, 'backend.event'),
                    'PUT' => new Method(Backend\Action\Event\Update::class, Backend\Model\Event_Update::class, [200 => Message::class], null, 'backend.event', 'fusio.event.update'),
                    'DELETE' => new Method(Backend\Action\Event\Delete::class, null, [200 => Message::class], null, 'backend.event', 'fusio.event.delete'),
                ],
                '/import/:format' => [
                    'POST' => new Method(Backend\Action\Import\Format::class, Backend\Model\Import_Request::class, [200 => Backend\Model\Adapter::class], null, 'backend.import'),
                ],
                '/import/process' => [
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
                '/log/$log_id<[0-9]+>' => [
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
                    'POST' => new Method(Backend\Action\Plan\Contract\Create::class, Backend\Model\Plan_Contract_Create::class, [201 => Message::class], null, 'backend.plan'),
                ],
                '/plan/contract/$contract_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Plan\Contract\Get::class, null, [200 => Backend\Model\Plan_Contract::class], null, 'backend.plan'),
                    'PUT' => new Method(Backend\Action\Plan\Contract\Update::class, Backend\Model\Plan_Contract_Update::class, [200 => Message::class], null, 'backend.plan'),
                    'DELETE' => new Method(Backend\Action\Plan\Contract\Delete::class, null, [200 => Message::class], null, 'backend.plan'),
                ],
                '/plan/invoice' => [
                    'GET' => new Method(Backend\Action\Plan\Invoice\GetAll::class, null, [200 => Backend\Model\Plan_Invoice_Collection::class], Collection_Query::class, 'backend.plan'),
                    'POST' => new Method(Backend\Action\Plan\Invoice\Create::class, Backend\Model\Plan_Invoice_Create::class, [201 => Message::class], null, 'backend.plan'),
                ],
                '/plan/invoice/$invoice_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Plan\Invoice\Get::class, null, [200 => Backend\Model\Plan_Invoice::class], null, 'backend.plan'),
                    'PUT' => new Method(Backend\Action\Plan\Invoice\Update::class, Backend\Model\Plan_Invoice_Update::class, [200 => Message::class], null, 'backend.plan'),
                    'DELETE' => new Method(Backend\Action\Plan\Invoice\Delete::class, null, [200 => Message::class], null, 'backend.plan'),
                ],
                '/plan' => [
                    'GET' => new Method(Backend\Action\Plan\GetAll::class, null, [200 => Backend\Model\Plan_Collection::class], Collection_Query::class, 'backend.plan'),
                    'POST' => new Method(Backend\Action\Plan\Create::class, Backend\Model\Plan_Create::class, [201 => Message::class], null, 'backend.plan', 'fusio.plan.create'),
                ],
                '/plan/$plan_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Plan\Get::class, null, [200 => Backend\Model\Plan::class], null, 'backend.plan'),
                    'PUT' => new Method(Backend\Action\Plan\Update::class, Backend\Model\Plan_Update::class, [200 => Message::class], null, 'backend.plan', 'fusio.plan.update'),
                    'DELETE' => new Method(Backend\Action\Plan\Delete::class, null, [200 => Message::class], null, 'backend.plan', 'fusio.plan.delete'),
                ],
                '/rate' => [
                    'GET' => new Method(Backend\Action\Rate\GetAll::class, null, [200 => Backend\Model\Rate_Collection::class], Collection_Query::class, 'backend.rate'),
                    'POST' => new Method(Backend\Action\Rate\Create::class, Backend\Model\Rate_Create::class, [201 => Message::class], null, 'backend.rate', 'fusio.rate.create'),
                ],
                '/rate/$rate_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Rate\Get::class, null, [200 => Backend\Model\Rate::class], null, 'backend.rate'),
                    'PUT' => new Method(Backend\Action\Rate\Update::class, Backend\Model\Rate_Update::class, [200 => Message::class], null, 'backend.rate', 'fusio.rate.update'),
                    'DELETE' => new Method(Backend\Action\Rate\Delete::class, null, [200 => Message::class], null, 'backend.rate', 'fusio.rate.delete'),
                ],
                '/routes' => [
                    'GET' => new Method(Backend\Action\Route\GetAll::class, null, [200 => Backend\Model\Route_Collection::class], Collection_Category_Query::class, 'backend.route'),
                    'POST' => new Method(Backend\Action\Route\Create::class, Backend\Model\Route_Create::class, [201 => Message::class], null, 'backend.route', 'fusio.route.create'),
                ],
                '/routes/provider' => [
                    'GET' => new Method(Backend\Action\Route\Provider\Index::class, null, [200 => Backend\Model\Route_Index_Providers::class], null, 'backend.route'),
                ],
                '/routes/provider/:provider' => [
                    'GET' => new Method(Backend\Action\Route\Provider\Form::class, null, [200 => Form_Container::class], null, 'backend.route'),
                    'POST' => new Method(Backend\Action\Route\Provider\Create::class, Backend\Model\Route_Provider::class, [201 => Message::class], null, 'backend.route'),
                    'PUT' => new Method(Backend\Action\Route\Provider\Changelog::class, Backend\Model\Route_Provider_Config::class, [200 => Backend\Model\Route_Provider_Changelog::class], null, 'backend.route'),
                ],
                '/routes/$route_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Route\Get::class, null, [200 => Backend\Model\Route::class], null, 'backend.route'),
                    'PUT' => new Method(Backend\Action\Route\Update::class, Backend\Model\Route_Update::class, [200 => Message::class], null, 'backend.route', 'fusio.route.update'),
                    'DELETE' => new Method(Backend\Action\Route\Delete::class, null, [200 => Message::class], null, 'backend.route', 'fusio.route.delete'),
                ],
                '/schema' => [
                    'GET' => new Method(Backend\Action\Schema\GetAll::class, null, [200 => Backend\Model\Schema_Collection::class], Collection_Category_Query::class, 'backend.schema'),
                    'POST' => new Method(Backend\Action\Schema\Create::class, Backend\Model\Schema_Create::class, [201 => Message::class], null, 'backend.schema', 'fusio.schema.create'),
                ],
                '/schema/preview/$schema_id<[0-9]+>' => [
                    'POST' => new Method(Backend\Action\Schema\GetPreview::class, null, [200 => Backend\Model\Schema_Preview_Response::class], null, 'backend.schema'),
                ],
                '/schema/form/$schema_id<[0-9]+>' => [
                    'PUT' => new Method(Backend\Action\Schema\Form::class, Backend\Model\Schema_Form::class, [200 => Message::class], null, 'backend.schema'),
                ],
                '/schema/$schema_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Schema\Get::class, null, [200 => Backend\Model\Schema::class], null, 'backend.schema'),
                    'PUT' => new Method(Backend\Action\Schema\Update::class, Backend\Model\Schema_Update::class, [200 => Message::class], null, 'backend.schema', 'fusio.schema.update'),
                    'DELETE' => new Method(Backend\Action\Schema\Delete::class, null, [200 => Message::class], null, 'backend.schema', 'fusio.schema.delete'),
                ],
                '/scope' => [
                    'GET' => new Method(Backend\Action\Scope\GetAll::class, null, [200 => Backend\Model\Scope_Collection::class], Collection_Category_Query::class, 'backend.scope'),
                    'POST' => new Method(Backend\Action\Scope\Create::class, Backend\Model\Scope_Create::class, [201 => Message::class], null, 'backend.scope', 'fusio.scope.create'),
                ],
                '/scope/$scope_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Scope\Get::class, null, [200 => Backend\Model\Scope::class], null, 'backend.scope'),
                    'PUT' => new Method(Backend\Action\Scope\Update::class, Backend\Model\Scope_Update::class, [200 => Message::class], null, 'backend.scope', 'fusio.scope.update'),
                    'DELETE' => new Method(Backend\Action\Scope\Delete::class, null, [200 => Message::class], null, 'backend.scope', 'fusio.scope.delete'),
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
                    'POST' => new Method(Backend\Action\User\Create::class, Backend\Model\User_Create::class, [201 => Message::class], null, 'backend.user', 'fusio.user.create'),
                ],
                '/user/$user_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\User\Get::class, null, [200 => Backend\Model\User::class], null, 'backend.user'),
                    'PUT' => new Method(Backend\Action\User\Update::class, Backend\Model\User_Update::class, [200 => Message::class], null, 'backend.user', 'fusio.user.update'),
                    'DELETE' => new Method(Backend\Action\User\Delete::class, null, [200 => Message::class], null, 'backend.user', 'fusio.user.delete'),
                ],
            ],
            'consumer' => [
                '/app' => [
                    'GET' => new Method(Consumer\Action\App\GetAll::class, null, [200 => Consumer\Model\App_Collection::class], Collection_Query::class, 'consumer.app'),
                    'POST' => new Method(Consumer\Action\App\Create::class, Consumer\Model\App_Create::class, [201 => Message::class], null, 'consumer.app'),
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
                    'DELETE' => new Method(Consumer\Action\Grant\Delete::class, null, [204 => Message::class], null, 'consumer.grant'),
                ],
                '/plan/contract' => [
                    'GET' => new Method(Consumer\Action\Plan\Contract\GetAll::class, null, [200 => Consumer\Model\Plan_Contract_Collection::class], Collection_Query::class, 'consumer.plan'),
                    'POST' => new Method(Consumer\Action\Plan\Contract\Create::class, Consumer\Model\Plan_Order_Request::class, [201 => Consumer\Model\Plan_Order_Response::class], null, 'consumer.plan'),
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
                    'GET' => new Method(Consumer\Action\Event\Subscription\GetAll::class, null, [200 => Consumer\Model\Event_Subscription_Collection::class], Collection_Query::class, 'consumer.subscription'),
                    'POST' => new Method(Consumer\Action\Event\Subscription\Create::class, Consumer\Model\Event_Subscription_Create::class, [201 => Message::class], null, 'consumer.subscription'),
                ],
                '/subscription/$subscription_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Event\Subscription\Get::class, null, [200 => Consumer\Model\Event_Subscription::class], null, 'consumer.subscription'),
                    'PUT' => new Method(Consumer\Action\Event\Subscription\Update::class, Consumer\Model\Event_Subscription_Update::class, [200 => Message::class], null, 'consumer.subscription'),
                    'DELETE' => new Method(Consumer\Action\Event\Subscription\Delete::class, null, [200 => Message::class], null, 'consumer.subscription'),
                ],
                '/transaction' => [
                    'GET' => new Method(Consumer\Action\Transaction\GetAll::class, null, [200 => Consumer\Model\Transaction_Collection::class], Collection_Query::class, 'consumer.transaction'),
                ],
                '/transaction/execute/:transaction_id' => [
                    'GET' => new Method(Consumer\Action\Transaction\Execute::class, null, [], null, 'consumer.transaction'),
                ],
                '/transaction/prepare/:provider' => [
                    'POST' => new Method(Consumer\Action\Transaction\Prepare::class, Consumer\Model\Transaction_Prepare_Request::class, [200 => Consumer\Model\Transaction_Prepare_Response::class], null, 'consumer.transaction'),
                ],
                '/transaction/$transaction_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Transaction\Get::class, null, [200 => Consumer\Model\Transaction::class], null, 'consumer.transaction'),
                ],
                '/account' => [
                    'GET' => new Method(Consumer\Action\User\Get::class, null, [200 => Consumer\Model\User_Account::class], null, 'consumer.user'),
                    'PUT' => new Method(Consumer\Action\User\Update::class, Consumer\Model\User_Account::class, [200 => Message::class], null, 'consumer.user'),
                ],
                '/account/change_password' => [
                    'PUT' => new Method(Consumer\Action\User\ChangePassword::class, Backend\Model\Account_ChangePassword::class, [200 => Message::class], null, 'consumer.user'),
                ],
                '/activate' => [
                    'POST' => new Method(Consumer\Action\User\Activate::class, Consumer\Model\User_Activate::class, [200 => Message::class], null, 'consumer.user', null, true),
                ],
                '/authorize' => [
                    'GET' => new Method(Consumer\Action\User\GetApp::class, null, [200 => Consumer\Model\Authorize_Meta::class], null, 'consumer.user', null, true),
                    'POST' => new Method(Consumer\Action\User\Authorize::class, Consumer\Model\Authorize_Request::class, [200 => Consumer\Model\Authorize_Response::class], null, 'consumer.user', null, true),
                ],
                '/login' => [
                    'POST' => new Method(Consumer\Action\User\Login::class, Consumer\Model\User_Login::class, [200 => Consumer\Model\User_JWT::class], null, 'consumer.user', null, true),
                    'PUT' => new Method(Consumer\Action\User\Refresh::class, Consumer\Model\User_Refresh::class, [200 => Consumer\Model\User_JWT::class], null, 'consumer.user', null, true),
                ],
                '/provider/:provider' => [
                    'POST' => new Method(Consumer\Action\User\Provider::class, Consumer\Model\User_Provider::class, [200 => Consumer\Model\User_JWT::class], null, 'consumer.user', null, true),
                ],
                '/register' => [
                    'POST' => new Method(Consumer\Action\User\Register::class, Consumer\Model\User_Register::class, [200 => Message::class], null, 'consumer.user', null, true),
                ],
                '/password_reset' => [
                    'POST' => new Method(Consumer\Action\User\ResetPassword\Request::class, Consumer\Model\User_Email::class, [200 => Message::class], null, 'consumer.user', null, true),
                    'PUT' => new Method(Consumer\Action\User\ResetPassword\Execute::class, Consumer\Model\User_PasswordReset::class, [200 => Message::class], null, 'consumer.user', null, true),
                ],
            ],
            'system' => [
                '/route' => [
                    'GET' => new Method(System\Action\GetAllRoute::class, null, [200 => System\Model\Route::class]),
                ],
                '/invoke/:method' => [
                    'POST' => new Method(System\Action\Invoke::class, 'Passthru', [200 => 'Passthru']),
                ],
                '/health' => [
                    'GET' => new Method(System\Action\GetHealth::class, null, [200 => System\Model\Health_Check::class]),
                ],
                '/debug' => [
                    'GET' => new Method(System\Action\GetDebug::class, null, [200 => System\Model\Debug::class]),
                    'POST' => new Method(System\Action\GetDebug::class, 'Passthru', [200 => System\Model\Debug::class]),
                    'PUT' => new Method(System\Action\GetDebug::class, 'Passthru', [200 => System\Model\Debug::class]),
                    'DELETE' => new Method(System\Action\GetDebug::class, null, [200 => System\Model\Debug::class]),
                    'PATCH' => new Method(System\Action\GetDebug::class, 'Passthru', [200 => System\Model\Debug::class]),
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
    }
}

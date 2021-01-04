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
use Fusio\Impl\Authorization;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Backend;
use Fusio\Impl\Connection\System as ConnectionSystem;
use Fusio\Impl\Consumer;
use Fusio\Impl\System;
use Fusio\Impl\Table;
use Fusio\Model;
use Fusio\Model\Collection_Category_Query;
use Fusio\Model\Collection_Query;
use Fusio\Model\Form_Container;
use Fusio\Model\Message;
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
        $bag->addRole('default', 'Administrator');
        $bag->addRole('default', 'Backend');
        $bag->addRole('default', 'Consumer');
        $bag->addUser('Administrator', 'Administrator', 'admin@localhost.com', $password);
        $bag->addApp('Administrator', 'Backend', 'https://www.fusio-project.org', $backendAppKey, $backendAppSecret);
        $bag->addApp('Administrator', 'Consumer', 'https://www.fusio-project.org', $consumerAppKey, $consumerAppSecret);
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
        $bag->addConfig('role_default', Table\Config::FORM_STRING, 'Consumer', 'Default role which a user gets assigned on registration');
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
        $bag->addAction('backend', 'Backend_Action_Action_Async', Backend\Action\Action\Async::class);
        $bag->addAction('backend', 'Backend_Action_Event_Execute', Backend\Action\Event\Execute::class);
        $bag->addAction('backend', 'Backend_Action_Plan_Billing', Backend\Action\Plan\Billing::class);
        $bag->addCronjob('backend', 'Execute_Async', '* * * * *', 'Backend_Action_Action_Async');
        $bag->addCronjob('backend', 'Dispatch_Event', '* * * * *', 'Backend_Action_Event_Execute');
        $bag->addCronjob('backend', 'Billing_Run', '0 0 * * *', 'Backend_Action_Plan_Billing');
        $bag->addRoleScope('Administrator', 'authorization');
        $bag->addRoleScope('Administrator', 'backend');
        $bag->addRoleScope('Administrator', 'consumer');
        $bag->addRoleScope('Backend', 'authorization');
        $bag->addRoleScope('Backend', 'backend');
        $bag->addRoleScope('Consumer', 'authorization');
        $bag->addRoleScope('Consumer', 'consumer');
        $bag->addRoute('system', 0, '/system/jsonrpc', System\Api\JsonRpc::class);
        $bag->addRoute('system', 2, '/system/doc', Tool\Documentation\IndexController::class);
        $bag->addRoute('system', 1, '/system/doc/:version/*path', Tool\Documentation\DetailController::class);
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
                    'GET' => new Method(System\Action\GetAbout::class, null, [200 => Model\System\About::class], null, null, null, true),
                ]
            ],
            'backend' => [
                '/account' => [
                    'GET' => new Method(Backend\Action\Account\Get::class, null, [200 => Model\Backend\User::class], null, 'backend.account'),
                    'PUT' => new Method(Backend\Action\Account\Update::class, Model\Backend\User_Update::class, [200 => Message::class], null, 'backend.account'),
                ],
                '/account/change_password' => [
                    'PUT' => new Method(Backend\Action\Account\ChangePassword::class, Model\Backend\Account_ChangePassword::class, [200 => Message::class], null, 'backend.account'),
                ],
                '/action' => [
                    'GET' => new Method(Backend\Action\Action\GetAll::class, null, [200 => Model\Backend\Action_Collection::class], Collection_Category_Query::class, 'backend.action'),
                    'POST' => new Method(Backend\Action\Action\Create::class, Model\Backend\Action_Create::class, [201 => Message::class], null, 'backend.action', 'fusio.action.create'),
                ],
                '/action/list' => [
                    'GET' => new Method(Backend\Action\Action\GetIndex::class, null, [200 => Model\Backend\Action_Index::class], null, 'backend.action'),
                ],
                '/action/form' => [
                    'GET' => new Method(Backend\Action\Action\GetForm::class, null, [200 => Form_Container::class], null, 'backend.action'),
                ],
                '/action/execute/:action_id' => [
                    'POST' => new Method(Backend\Action\Action\Execute::class, Model\Backend\Action_Execute_Request::class, [200 => Model\Backend\Action_Execute_Response::class], null, 'backend.action'),
                ],
                '/action/$action_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Action\Get::class, null, [200 => Model\Backend\Action::class], null, 'backend.action'),
                    'PUT' => new Method(Backend\Action\Action\Update::class, Model\Backend\Action_Update::class, [200 => Message::class], null, 'backend.action', 'fusio.action.update'),
                    'DELETE' => new Method(Backend\Action\Action\Delete::class, null, [200 => Message::class], null, 'backend.action', 'fusio.action.delete'),
                ],
                '/app/token' => [
                    'GET' => new Method(Backend\Action\App\Token\GetAll::class, null, [200 => Model\Backend\App_Token_Collection::class], Model\Backend\App_Token_Collection_Query::class, 'backend.app'),
                ],
                '/app/token/$token_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\App\Token\Get::class, null, [200 => Model\Backend\App_Token::class], null, 'backend.app'),
                ],
                '/app' => [
                    'GET' => new Method(Backend\Action\App\GetAll::class, null, [200 => Model\Backend\App_Collection::class], Collection_Query::class, 'backend.app'),
                    'POST' => new Method(Backend\Action\App\Create::class, Model\Backend\App_Create::class, [201 => Message::class], null, 'backend.app', 'fusio.app.create'),
                ],
                '/app/$app_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\App\Get::class, null, [200 => Model\Backend\App::class], null, 'backend.app'),
                    'PUT' => new Method(Backend\Action\App\Update::class, Model\Backend\App_Update::class, [200 => Message::class], null, 'backend.app', 'fusio.app.update'),
                    'DELETE' => new Method(Backend\Action\App\Delete::class, null, [200 => Message::class], null, 'backend.app', 'fusio.app.delete'),
                ],
                '/app/$app_id<[0-9]+>/token/:token_id' => [
                    'DELETE' => new Method(Backend\Action\App\DeleteToken::class, null, [200 => Message::class], null, 'backend.app'),
                ],
                '/audit' => [
                    'GET' => new Method(Backend\Action\Audit\GetAll::class, null, [200 => Model\Backend\Audit_Collection::class], Model\Backend\Audit_Collection_Query::class, 'backend.audit'),
                ],
                '/audit/$audit_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Audit\Get::class, null, [200 => Model\Backend\Audit::class], null, 'backend.audit'),
                ],
                '/category' => [
                    'GET' => new Method(Backend\Action\Category\GetAll::class, null, [200 => Model\Backend\Category_Collection::class], Collection_Query::class, 'backend.category'),
                    'POST' => new Method(Backend\Action\Category\Create::class, Model\Backend\Category_Create::class, [201 => Message::class], null, 'backend.category', 'fusio.category.create'),
                ],
                '/category/$category_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Category\Get::class, null, [200 => Model\Backend\Category::class], null, 'backend.category'),
                    'PUT' => new Method(Backend\Action\Category\Update::class, Model\Backend\Category_Update::class, [200 => Message::class], null, 'backend.category', 'fusio.category.update'),
                    'DELETE' => new Method(Backend\Action\Category\Delete::class, null, [200 => Message::class], null, 'backend.category', 'fusio.category.delete'),
                ],
                '/config' => [
                    'GET' => new Method(Backend\Action\Config\GetAll::class, null, [200 => Model\Backend\Config_Collection::class], Collection_Query::class, 'backend.config'),
                ],
                '/config/$config_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Config\Get::class, null, [200 => Model\Backend\Config::class], null, 'backend.config'),
                    'PUT' => new Method(Backend\Action\Config\Update::class, Model\Backend\Config_Update::class, [200 => Message::class], null, 'backend.config'),
                ],
                '/connection' => [
                    'GET' => new Method(Backend\Action\Connection\GetAll::class, null, [200 => Model\Backend\Connection_Collection::class], Collection_Query::class, 'backend.connection'),
                    'POST' => new Method(Backend\Action\Connection\Create::class, Model\Backend\Connection_Create::class, [201 => Message::class], null, 'backend.connection', 'fusio.connection.create'),
                ],
                '/connection/list' => [
                    'GET' => new Method(Backend\Action\Connection\GetIndex::class, null, [200 => Model\Backend\Connection_Index::class], null, 'backend.connection'),
                ],
                '/connection/form' => [
                    'GET' => new Method(Backend\Action\Connection\GetForm::class, null, [200 => Form_Container::class], null, 'backend.connection'),
                ],
                '/connection/$connection_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Connection\Get::class, null, [200 => Model\Backend\Connection::class], null, 'backend.connection'),
                    'PUT' => new Method(Backend\Action\Connection\Update::class, Model\Backend\Connection_Update::class, [200 => Message::class], null, 'backend.connection', 'fusio.connection.update'),
                    'DELETE' => new Method(Backend\Action\Connection\Delete::class, null, [200 => Message::class], null, 'backend.connection', 'fusio.connection.delete'),
                ],
                '/cronjob' => [
                    'GET' => new Method(Backend\Action\Cronjob\GetAll::class, null, [200 => Model\Backend\Cronjob_Collection::class], Collection_Category_Query::class, 'backend.cronjob'),
                    'POST' => new Method(Backend\Action\Cronjob\Create::class, Model\Backend\Cronjob_Create::class, [201 => Message::class], null, 'backend.cronjob', 'fusio.cronjob.create'),
                ],
                '/cronjob/$cronjob_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Cronjob\Get::class, null, [200 => Model\Backend\Cronjob::class], null, 'backend.cronjob'),
                    'PUT' => new Method(Backend\Action\Cronjob\Update::class, Model\Backend\Cronjob_Update::class, [200 => Message::class], null, 'backend.cronjob', 'fusio.cronjob.update'),
                    'DELETE' => new Method(Backend\Action\Cronjob\Delete::class, null, [200 => Message::class], null, 'backend.cronjob', 'fusio.cronjob.delete'),
                ],
                '/dashboard' => [
                    'GET' => new Method(Backend\Action\Dashboard\GetAll::class, null, [200 => Model\Backend\Dashboard::class], null, 'backend.dashboard'),
                ],
                '/event/subscription' => [
                    'GET' => new Method(Backend\Action\Event\Subscription\GetAll::class, null, [200 => Model\Backend\Event_Subscription_Collection::class], Collection_Query::class, 'backend.event'),
                    'POST' => new Method(Backend\Action\Event\Subscription\Create::class, Model\Backend\Event_Subscription_Create::class, [201 => Message::class], null, 'backend.event', 'fusio.event.subscription.create'),
                ],
                '/event/subscription/$subscription_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Event\Subscription\Get::class, null, [200 => Model\Backend\Event_Subscription::class], null, 'backend.event'),
                    'PUT' => new Method(Backend\Action\Event\Subscription\Update::class, Model\Backend\Event_Subscription_Update::class, [200 => Message::class], null, 'backend.event', 'fusio.event.subscription.update'),
                    'DELETE' => new Method(Backend\Action\Event\Subscription\Delete::class, null, [200 => Message::class], null, 'backend.event', 'fusio.event.subscription.delete'),
                ],
                '/event' => [
                    'GET' => new Method(Backend\Action\Event\GetAll::class, null, [200 => Model\Backend\Event_Collection::class], Collection_Category_Query::class, 'backend.event'),
                    'POST' => new Method(Backend\Action\Event\Create::class, Model\Backend\Event_Create::class, [201 => Message::class], null, 'backend.event', 'fusio.event.create'),
                ],
                '/event/$event_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Event\Get::class, null, [200 => Model\Backend\Event::class], null, 'backend.event'),
                    'PUT' => new Method(Backend\Action\Event\Update::class, Model\Backend\Event_Update::class, [200 => Message::class], null, 'backend.event', 'fusio.event.update'),
                    'DELETE' => new Method(Backend\Action\Event\Delete::class, null, [200 => Message::class], null, 'backend.event', 'fusio.event.delete'),
                ],
                '/log/error' => [
                    'GET' => new Method(Backend\Action\Log\Error\GetAll::class, null, [200 => Model\Backend\Log_Error_Collection::class], Collection_Query::class, 'backend.log'),
                ],
                '/log/error/$error_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Log\Error\Get::class, null, [200 => Model\Backend\Log_Error::class], null, 'backend.log'),
                ],
                '/log' => [
                    'GET' => new Method(Backend\Action\Log\GetAll::class, null, [200 => Model\Backend\Log_Collection::class], Model\Backend\Log_Collection_Query::class, 'backend.log'),
                ],
                '/log/$log_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Log\Get::class, null, [200 => Model\Backend\Log::class], null, 'backend.log'),
                ],
                '/marketplace' => [
                    'GET' => new Method(Backend\Action\Marketplace\GetAll::class, null, [200 => Model\Backend\Marketplace_Collection::class], null, 'backend.marketplace'),
                    'POST' => new Method(Backend\Action\Marketplace\Install::class, Model\Backend\Marketplace_Install::class, [201 => Model\Backend\Marketplace_Install::class], null, 'backend.marketplace'),
                ],
                '/marketplace/:app_name' => [
                    'GET' => new Method(Backend\Action\Marketplace\Get::class, null, [200 => Model\Backend\Marketplace_Local_App::class], null, 'backend.marketplace'),
                    'PUT' => new Method(Backend\Action\Marketplace\Update::class, null, [200 => Message::class], null, 'backend.marketplace'),
                    'DELETE' => new Method(Backend\Action\Marketplace\Remove::class, null, [200 => Message::class], null, 'backend.marketplace'),
                ],
                '/plan/contract' => [
                    'GET' => new Method(Backend\Action\Plan\Contract\GetAll::class, null, [200 => Model\Backend\Plan_Contract_Collection::class], Collection_Query::class, 'backend.plan'),
                    'POST' => new Method(Backend\Action\Plan\Contract\Create::class, Model\Backend\Plan_Contract_Create::class, [201 => Message::class], null, 'backend.plan'),
                ],
                '/plan/contract/$contract_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Plan\Contract\Get::class, null, [200 => Model\Backend\Plan_Contract::class], null, 'backend.plan'),
                    'PUT' => new Method(Backend\Action\Plan\Contract\Update::class, Model\Backend\Plan_Contract_Update::class, [200 => Message::class], null, 'backend.plan'),
                    'DELETE' => new Method(Backend\Action\Plan\Contract\Delete::class, null, [200 => Message::class], null, 'backend.plan'),
                ],
                '/plan/invoice' => [
                    'GET' => new Method(Backend\Action\Plan\Invoice\GetAll::class, null, [200 => Model\Backend\Plan_Invoice_Collection::class], Collection_Query::class, 'backend.plan'),
                    'POST' => new Method(Backend\Action\Plan\Invoice\Create::class, Model\Backend\Plan_Invoice_Create::class, [201 => Message::class], null, 'backend.plan'),
                ],
                '/plan/invoice/$invoice_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Plan\Invoice\Get::class, null, [200 => Model\Backend\Plan_Invoice::class], null, 'backend.plan'),
                    'PUT' => new Method(Backend\Action\Plan\Invoice\Update::class, Model\Backend\Plan_Invoice_Update::class, [200 => Message::class], null, 'backend.plan'),
                    'DELETE' => new Method(Backend\Action\Plan\Invoice\Delete::class, null, [200 => Message::class], null, 'backend.plan'),
                ],
                '/plan' => [
                    'GET' => new Method(Backend\Action\Plan\GetAll::class, null, [200 => Model\Backend\Plan_Collection::class], Collection_Query::class, 'backend.plan'),
                    'POST' => new Method(Backend\Action\Plan\Create::class, Model\Backend\Plan_Create::class, [201 => Message::class], null, 'backend.plan', 'fusio.plan.create'),
                ],
                '/plan/$plan_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Plan\Get::class, null, [200 => Model\Backend\Plan::class], null, 'backend.plan'),
                    'PUT' => new Method(Backend\Action\Plan\Update::class, Model\Backend\Plan_Update::class, [200 => Message::class], null, 'backend.plan', 'fusio.plan.update'),
                    'DELETE' => new Method(Backend\Action\Plan\Delete::class, null, [200 => Message::class], null, 'backend.plan', 'fusio.plan.delete'),
                ],
                '/rate' => [
                    'GET' => new Method(Backend\Action\Rate\GetAll::class, null, [200 => Model\Backend\Rate_Collection::class], Collection_Query::class, 'backend.rate'),
                    'POST' => new Method(Backend\Action\Rate\Create::class, Model\Backend\Rate_Create::class, [201 => Message::class], null, 'backend.rate', 'fusio.rate.create'),
                ],
                '/rate/$rate_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Rate\Get::class, null, [200 => Model\Backend\Rate::class], null, 'backend.rate'),
                    'PUT' => new Method(Backend\Action\Rate\Update::class, Model\Backend\Rate_Update::class, [200 => Message::class], null, 'backend.rate', 'fusio.rate.update'),
                    'DELETE' => new Method(Backend\Action\Rate\Delete::class, null, [200 => Message::class], null, 'backend.rate', 'fusio.rate.delete'),
                ],
                '/role' => [
                    'GET' => new Method(Backend\Action\Role\GetAll::class, null, [200 => Model\Backend\Role_Collection::class], Collection_Query::class, 'backend.role'),
                    'POST' => new Method(Backend\Action\Role\Create::class, Model\Backend\Role_Create::class, [201 => Message::class], null, 'backend.role', 'fusio.role.create'),
                ],
                '/role/$role_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Role\Get::class, null, [200 => Model\Backend\Role::class], null, 'backend.role'),
                    'PUT' => new Method(Backend\Action\Role\Update::class, Model\Backend\Role_Update::class, [200 => Message::class], null, 'backend.role', 'fusio.role.update'),
                    'DELETE' => new Method(Backend\Action\Role\Delete::class, null, [200 => Message::class], null, 'backend.role', 'fusio.role.delete'),
                ],
                '/routes' => [
                    'GET' => new Method(Backend\Action\Route\GetAll::class, null, [200 => Model\Backend\Route_Collection::class], Collection_Category_Query::class, 'backend.route'),
                    'POST' => new Method(Backend\Action\Route\Create::class, Model\Backend\Route_Create::class, [201 => Message::class], null, 'backend.route', 'fusio.route.create'),
                ],
                '/routes/provider' => [
                    'GET' => new Method(Backend\Action\Route\Provider\Index::class, null, [200 => Model\Backend\Route_Index_Providers::class], null, 'backend.route'),
                ],
                '/routes/provider/:provider' => [
                    'GET' => new Method(Backend\Action\Route\Provider\Form::class, null, [200 => Form_Container::class], null, 'backend.route'),
                    'POST' => new Method(Backend\Action\Route\Provider\Create::class, Model\Backend\Route_Provider::class, [201 => Message::class], null, 'backend.route'),
                    'PUT' => new Method(Backend\Action\Route\Provider\Changelog::class, Model\Backend\Route_Provider_Config::class, [200 => Model\Backend\Route_Provider_Changelog::class], null, 'backend.route'),
                ],
                '/routes/$route_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Route\Get::class, null, [200 => Model\Backend\Route::class], null, 'backend.route'),
                    'PUT' => new Method(Backend\Action\Route\Update::class, Model\Backend\Route_Update::class, [200 => Message::class], null, 'backend.route', 'fusio.route.update'),
                    'DELETE' => new Method(Backend\Action\Route\Delete::class, null, [200 => Message::class], null, 'backend.route', 'fusio.route.delete'),
                ],
                '/schema' => [
                    'GET' => new Method(Backend\Action\Schema\GetAll::class, null, [200 => Model\Backend\Schema_Collection::class], Collection_Category_Query::class, 'backend.schema'),
                    'POST' => new Method(Backend\Action\Schema\Create::class, Model\Backend\Schema_Create::class, [201 => Message::class], null, 'backend.schema', 'fusio.schema.create'),
                ],
                '/schema/preview/:schema_id' => [
                    'POST' => new Method(Backend\Action\Schema\GetPreview::class, null, [200 => Model\Backend\Schema_Preview_Response::class], null, 'backend.schema'),
                ],
                '/schema/form/$schema_id<[0-9]+>' => [
                    'PUT' => new Method(Backend\Action\Schema\Form::class, Model\Backend\Schema_Form::class, [200 => Message::class], null, 'backend.schema'),
                ],
                '/schema/$schema_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Schema\Get::class, null, [200 => Model\Backend\Schema::class], null, 'backend.schema'),
                    'PUT' => new Method(Backend\Action\Schema\Update::class, Model\Backend\Schema_Update::class, [200 => Message::class], null, 'backend.schema', 'fusio.schema.update'),
                    'DELETE' => new Method(Backend\Action\Schema\Delete::class, null, [200 => Message::class], null, 'backend.schema', 'fusio.schema.delete'),
                ],
                '/scope' => [
                    'GET' => new Method(Backend\Action\Scope\GetAll::class, null, [200 => Model\Backend\Scope_Collection::class], Collection_Category_Query::class, 'backend.scope'),
                    'POST' => new Method(Backend\Action\Scope\Create::class, Model\Backend\Scope_Create::class, [201 => Message::class], null, 'backend.scope', 'fusio.scope.create'),
                ],
                '/scope/categories' => [
                    'GET' => new Method(Backend\Action\Scope\GetCategories::class, null, [200 => Model\Backend\Scope_Categories::class], null, 'backend.scope'),
                ],
                '/scope/$scope_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Scope\Get::class, null, [200 => Model\Backend\Scope::class], null, 'backend.scope'),
                    'PUT' => new Method(Backend\Action\Scope\Update::class, Model\Backend\Scope_Update::class, [200 => Message::class], null, 'backend.scope', 'fusio.scope.update'),
                    'DELETE' => new Method(Backend\Action\Scope\Delete::class, null, [200 => Message::class], null, 'backend.scope', 'fusio.scope.delete'),
                ],
                '/sdk' => [
                    'GET' => new Method(Backend\Action\Sdk\GetAll::class, null, [200 => Model\Backend\Sdk_Types::class], null, 'backend.sdk'),
                    'POST' => new Method(Backend\Action\Sdk\Generate::class, Model\Backend\Sdk_Generate::class, [200 => Message::class], null, 'backend.sdk'),
                ],
                '/statistic/count_requests' => [
                    'GET' => new Method(Backend\Action\Statistic\GetCountRequests::class, null, [200 => Model\Backend\Statistic_Count::class], Model\Backend\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/errors_per_route' => [
                    'GET' => new Method(Backend\Action\Statistic\GetErrorsPerRoute::class, null, [200 => Model\Backend\Statistic_Count::class], Model\Backend\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/incoming_requests' => [
                    'GET' => new Method(Backend\Action\Statistic\GetIncomingRequests::class, null, [200 => Model\Backend\Statistic_Count::class], Model\Backend\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/incoming_transactions' => [
                    'GET' => new Method(Backend\Action\Statistic\GetIncomingTransactions::class, null, [200 => Model\Backend\Statistic_Count::class], Model\Backend\Transaction_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/issued_tokens' => [
                    'GET' => new Method(Backend\Action\Statistic\GetIssuedTokens::class, null, [200 => Model\Backend\Statistic_Count::class], Model\Backend\App_Token_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/most_used_apps' => [
                    'GET' => new Method(Backend\Action\Statistic\GetMostUsedApps::class, null, [200 => Model\Backend\Statistic_Count::class], Model\Backend\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/most_used_routes' => [
                    'GET' => new Method(Backend\Action\Statistic\GetMostUsedRoutes::class, null, [200 => Model\Backend\Statistic_Count::class], Model\Backend\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/time_average' => [
                    'GET' => new Method(Backend\Action\Statistic\GetTimeAverage::class, null, [200 => Model\Backend\Statistic_Count::class], Model\Backend\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/time_per_route' => [
                    'GET' => new Method(Backend\Action\Statistic\GetTimePerRoute::class, null, [200 => Model\Backend\Statistic_Count::class], Model\Backend\Log_Collection_Query::class, 'backend.statistic'),
                ],
                '/statistic/used_points' => [
                    'GET' => new Method(Backend\Action\Statistic\GetUsedPoints::class, null, [200 => Model\Backend\Statistic_Count::class], Model\Backend\Plan_Usage_Collection_Query::class, 'backend.statistic'),
                ],
                '/transaction' => [
                    'GET' => new Method(Backend\Action\Transaction\GetAll::class, null, [200 => Model\Backend\Transaction_Collection::class], Model\Backend\Transaction_Collection_Query::class, 'backend.transaction'),
                ],
                '/transaction/$transaction_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Transaction\Get::class, null, [200 => Model\Backend\Transaction::class], null, 'backend.transaction'),
                ],
                '/user' => [
                    'GET' => new Method(Backend\Action\User\GetAll::class, null, [200 => Model\Backend\User_Collection::class], Collection_Query::class, 'backend.user'),
                    'POST' => new Method(Backend\Action\User\Create::class, Model\Backend\User_Create::class, [201 => Message::class], null, 'backend.user', 'fusio.user.create'),
                ],
                '/user/$user_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\User\Get::class, null, [200 => Model\Backend\User::class], null, 'backend.user'),
                    'PUT' => new Method(Backend\Action\User\Update::class, Model\Backend\User_Update::class, [200 => Message::class], null, 'backend.user', 'fusio.user.update'),
                    'DELETE' => new Method(Backend\Action\User\Delete::class, null, [200 => Message::class], null, 'backend.user', 'fusio.user.delete'),
                ],
            ],
            'consumer' => [
                '/app' => [
                    'GET' => new Method(Consumer\Action\App\GetAll::class, null, [200 => Model\Consumer\App_Collection::class], Collection_Query::class, 'consumer.app'),
                    'POST' => new Method(Consumer\Action\App\Create::class, Model\Consumer\App_Create::class, [201 => Message::class], null, 'consumer.app'),
                ],
                '/app/$app_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\App\Get::class, null, [200 => Model\Consumer\App::class], null, 'consumer.app'),
                    'PUT' => new Method(Consumer\Action\App\Update::class, Model\Consumer\App_Update::class, [200 => Message::class], null, 'consumer.app'),
                    'DELETE' => new Method(Consumer\Action\App\Delete::class, null, [200 => Message::class], null, 'consumer.app'),
                ],
                '/event' => [
                    'GET' => new Method(Consumer\Action\Event\GetAll::class, null, [200 => Model\Consumer\Event_Collection::class], Collection_Query::class, 'consumer.event'),
                ],
                '/grant' => [
                    'GET' => new Method(Consumer\Action\Grant\GetAll::class, null, [200 => Model\Consumer\Grant_Collection::class], Collection_Query::class, 'consumer.grant'),
                ],
                '/grant/$grant_id<[0-9]+>' => [
                    'DELETE' => new Method(Consumer\Action\Grant\Delete::class, null, [204 => Message::class], null, 'consumer.grant'),
                ],
                '/plan/contract' => [
                    'GET' => new Method(Consumer\Action\Plan\Contract\GetAll::class, null, [200 => Model\Consumer\Plan_Contract_Collection::class], Collection_Query::class, 'consumer.plan'),
                    'POST' => new Method(Consumer\Action\Plan\Contract\Create::class, Model\Consumer\Plan_Order_Request::class, [201 => Model\Consumer\Plan_Order_Response::class], null, 'consumer.plan'),
                ],
                '/plan/contract/$contract_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Plan\Contract\Get::class, null, [200 => Model\Consumer\Plan_Contract::class], null, 'consumer.plan'),
                ],
                '/plan/invoice' => [
                    'GET' => new Method(Consumer\Action\Plan\Invoice\GetAll::class, null, [200 => Model\Consumer\Plan_Invoice_Collection::class], Collection_Query::class, 'consumer.plan'),
                ],
                '/plan/invoice/$invoice_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Plan\Invoice\Get::class, null, [200 => Model\Consumer\Plan_Invoice::class], null, 'consumer.plan'),
                ],
                '/plan' => [
                    'GET' => new Method(Consumer\Action\Plan\GetAll::class, null, [200 => Model\Consumer\Plan_Collection::class], Collection_Query::class, 'consumer.plan'),
                ],
                '/plan/$plan_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Plan\Get::class, null, [200 => Model\Consumer\Plan::class], null, 'consumer.plan'),
                ],
                '/scope' => [
                    'GET' => new Method(Consumer\Action\Scope\GetAll::class, null, [200 => Model\Consumer\Scope_Collection::class], Collection_Query::class, 'consumer.scope'),
                ],
                '/subscription' => [
                    'GET' => new Method(Consumer\Action\Event\Subscription\GetAll::class, null, [200 => Model\Consumer\Event_Subscription_Collection::class], Collection_Query::class, 'consumer.subscription'),
                    'POST' => new Method(Consumer\Action\Event\Subscription\Create::class, Model\Consumer\Event_Subscription_Create::class, [201 => Message::class], null, 'consumer.subscription'),
                ],
                '/subscription/$subscription_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Event\Subscription\Get::class, null, [200 => Model\Consumer\Event_Subscription::class], null, 'consumer.subscription'),
                    'PUT' => new Method(Consumer\Action\Event\Subscription\Update::class, Model\Consumer\Event_Subscription_Update::class, [200 => Message::class], null, 'consumer.subscription'),
                    'DELETE' => new Method(Consumer\Action\Event\Subscription\Delete::class, null, [200 => Message::class], null, 'consumer.subscription'),
                ],
                '/transaction' => [
                    'GET' => new Method(Consumer\Action\Transaction\GetAll::class, null, [200 => Model\Consumer\Transaction_Collection::class], Collection_Query::class, 'consumer.transaction'),
                ],
                '/transaction/execute/:transaction_id' => [
                    'GET' => new Method(Consumer\Action\Transaction\Execute::class, null, [], null, 'consumer.transaction'),
                ],
                '/transaction/prepare/:provider' => [
                    'POST' => new Method(Consumer\Action\Transaction\Prepare::class, Model\Consumer\Transaction_Prepare_Request::class, [200 => Model\Consumer\Transaction_Prepare_Response::class], null, 'consumer.transaction'),
                ],
                '/transaction/$transaction_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Transaction\Get::class, null, [200 => Model\Consumer\Transaction::class], null, 'consumer.transaction'),
                ],
                '/account' => [
                    'GET' => new Method(Consumer\Action\User\Get::class, null, [200 => Model\Consumer\User_Account::class], null, 'consumer.user'),
                    'PUT' => new Method(Consumer\Action\User\Update::class, Model\Consumer\User_Account::class, [200 => Message::class], null, 'consumer.user'),
                ],
                '/account/change_password' => [
                    'PUT' => new Method(Consumer\Action\User\ChangePassword::class, Model\Backend\Account_ChangePassword::class, [200 => Message::class], null, 'consumer.user'),
                ],
                '/activate' => [
                    'POST' => new Method(Consumer\Action\User\Activate::class, Model\Consumer\User_Activate::class, [200 => Message::class], null, 'consumer.user', null, true),
                ],
                '/authorize' => [
                    'GET' => new Method(Consumer\Action\User\GetApp::class, null, [200 => Model\Consumer\Authorize_Meta::class], null, 'consumer.user', null, true),
                    'POST' => new Method(Consumer\Action\User\Authorize::class, Model\Consumer\Authorize_Request::class, [200 => Model\Consumer\Authorize_Response::class], null, 'consumer.user', null, true),
                ],
                '/login' => [
                    'POST' => new Method(Consumer\Action\User\Login::class, Model\Consumer\User_Login::class, [200 => Model\Consumer\User_JWT::class], null, 'consumer.user', null, true),
                    'PUT' => new Method(Consumer\Action\User\Refresh::class, Model\Consumer\User_Refresh::class, [200 => Model\Consumer\User_JWT::class], null, 'consumer.user', null, true),
                ],
                '/provider/:provider' => [
                    'POST' => new Method(Consumer\Action\User\Provider::class, Model\Consumer\User_Provider::class, [200 => Model\Consumer\User_JWT::class], null, 'consumer.user', null, true),
                ],
                '/register' => [
                    'POST' => new Method(Consumer\Action\User\Register::class, Model\Consumer\User_Register::class, [200 => Message::class], null, 'consumer.user', null, true),
                ],
                '/password_reset' => [
                    'POST' => new Method(Consumer\Action\User\ResetPassword\Request::class, Model\Consumer\User_Email::class, [200 => Message::class], null, 'consumer.user', null, true),
                    'PUT' => new Method(Consumer\Action\User\ResetPassword\Execute::class, Model\Consumer\User_PasswordReset::class, [200 => Message::class], null, 'consumer.user', null, true),
                ],
            ],
            'system' => [
                '/about' => [
                    'GET' => new Method(System\Action\GetAbout::class, null, [200 => Model\System\About::class], null, null, null, true),
                ],
                '/route' => [
                    'GET' => new Method(System\Action\GetAllRoute::class, null, [200 => Model\System\Route::class], null, null, null, true),
                ],
                '/health' => [
                    'GET' => new Method(System\Action\GetHealth::class, null, [200 => Model\System\Health_Check::class], null, null, null, true),
                ],
                '/debug' => [
                    'GET' => new Method(System\Action\GetDebug::class, null, [200 => Model\System\Debug::class], null, null, null, true),
                    'POST' => new Method(System\Action\GetDebug::class, 'Passthru', [200 => Model\System\Debug::class], null, null, null, true),
                    'PUT' => new Method(System\Action\GetDebug::class, 'Passthru', [200 => Model\System\Debug::class], null, null, null, true),
                    'DELETE' => new Method(System\Action\GetDebug::class, null, [200 => Model\System\Debug::class], null, null, null, true),
                    'PATCH' => new Method(System\Action\GetDebug::class, 'Passthru', [200 => Model\System\Debug::class], null, null, null, true),
                ],
                '/schema/:name' => [
                    'GET' => new Method(System\Action\GetSchema::class, null, [200 => Model\System\Schema::class], null, null, null, true),
                ],
            ],
            'authorization' => [
                '/revoke' => [
                    'POST' => new Method(Authorization\Action\Revoke::class, null, [200 => Message::class], null, 'authorization'),
                ],
                '/whoami' => [
                    'GET' => new Method(Authorization\Action\GetWhoami::class, null, [200 => Model\Backend\User::class], null, 'authorization'),
                ],
            ],
        ];
    }
}

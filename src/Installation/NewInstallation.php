<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Installation;

use Fusio\Adapter;
use Fusio\Impl\Authorization;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Backend;
use Fusio\Impl\Connection\System as ConnectionSystem;
use Fusio\Impl\Consumer;
use Fusio\Impl\System;
use Fusio\Impl\Table;
use Fusio\Model;
use Fusio\Model\Message;
use PSX\Api\Model\Passthru;
use PSX\Schema\Format;
use PSX\Schema\TypeFactory;

/**
 * NewInstallation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class NewInstallation
{
    private static ?DataBag $data = null;

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
        $bag->addScope('default', 'default', 'Default scope');
        $bag->addAppScope('Backend', 'backend');
        $bag->addAppScope('Backend', 'authorization');
        $bag->addAppScope('Backend', 'default');
        $bag->addAppScope('Consumer', 'consumer');
        $bag->addAppScope('Consumer', 'authorization');
        $bag->addAppScope('Consumer', 'default');
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
        $bag->addConfig('mail_register_body', Table\Config::FORM_TEXT, 'Hello {name},' . "\n\n" . 'you have successful registered at Fusio.' . "\n" . 'To activate you account please visit the following link:' . "\n" . '{apps_url}/developer/register/activate/{token}', 'Body of the activation mail');
        $bag->addConfig('mail_pw_reset_subject', Table\Config::FORM_STRING, 'Fusio password reset', 'Subject of the password reset mail');
        $bag->addConfig('mail_pw_reset_body', Table\Config::FORM_TEXT, 'Hello {name},' . "\n\n" . 'you have requested to reset your password.' . "\n" . 'To set a new password please visit the following link:' . "\n" . '{apps_url}/developer/password/confirm/{token}' . "\n\n" . 'Please ignore this email if you have not requested a password reset.', 'Body of the password reset mail');
        $bag->addConfig('mail_points_subject', Table\Config::FORM_STRING, 'Fusio points threshold reached', 'Subject of the points threshold mail');
        $bag->addConfig('mail_points_body', Table\Config::FORM_TEXT, 'Hello {name},' . "\n\n" . 'your account has reached the configured threshold of {points} points.' . "\n" . 'If your account reaches 0 points your are not longer able to invoke specific endpoints.' . "\n" . 'To prevent this please go to the developer portal to purchase new points:' . "\n" . '{apps_url}/developer', 'Body of the points threshold mail');
        $bag->addConfig('provider_facebook_key', Table\Config::FORM_STRING, '', 'Facebook app key');
        $bag->addConfig('provider_facebook_secret', Table\Config::FORM_STRING, '', 'Facebook app secret');
        $bag->addConfig('provider_google_key', Table\Config::FORM_STRING, '', 'Google app key');
        $bag->addConfig('provider_google_secret', Table\Config::FORM_STRING, '', 'Google app secret');
        $bag->addConfig('provider_github_key', Table\Config::FORM_STRING, '', 'GitHub app key');
        $bag->addConfig('provider_github_secret', Table\Config::FORM_STRING, '', 'GitHub app secret');
        $bag->addConfig('recaptcha_key', Table\Config::FORM_STRING, '', 'ReCaptcha key');
        $bag->addConfig('recaptcha_secret', Table\Config::FORM_STRING, '', 'ReCaptcha secret');
        $bag->addConfig('payment_stripe_secret', Table\Config::FORM_STRING, '', 'The stripe webhook secret which is needed to verify a webhook request');
        $bag->addConfig('payment_stripe_portal_configuration', Table\Config::FORM_STRING, '', 'The stripe portal configuration id');
        $bag->addConfig('payment_currency', Table\Config::FORM_STRING, '', 'The three-character ISO-4217 currency code which is used to process payments');
        $bag->addConfig('role_default', Table\Config::FORM_STRING, 'Consumer', 'Default role which a user gets assigned on registration');
        $bag->addConfig('points_default', Table\Config::FORM_NUMBER, 0, 'The default amount of points which a user receives if he registers');
        $bag->addConfig('points_threshold', Table\Config::FORM_NUMBER, 0, 'If a user goes below this points threshold we send an information to the user');
        $bag->addConfig('system_mailer', Table\Config::FORM_STRING, '', 'Optional a SMTP connection which is used as mailer');
        $bag->addConfig('system_dispatcher', Table\Config::FORM_STRING, '', 'Optional a HTTP or message queue connection which is used to dispatch events');
        $bag->addConfig('user_pw_length', Table\Config::FORM_NUMBER, 8, 'Minimal required password length');
        $bag->addConfig('user_approval', Table\Config::FORM_BOOLEAN, 1, 'Whether the user needs to activate the account through an email');
        $bag->addConnection('System', ConnectionSystem::class);
        $bag->addRate('Default', 0, 3600, 'PT1H');
        $bag->addRate('Default-Anonymous', 4, 900, 'PT1H');
        $bag->addRateAllocation('Default');
        $bag->addRateAllocation('Default-Anonymous', null, null, null, null, false);
        $bag->addAction('backend', 'Backend_Action_Action_Async', Backend\Action\Action\Async::class);
        $bag->addAction('backend', 'Backend_Action_Event_Execute', Backend\Action\Event\Execute::class);
        $bag->addAction('backend', 'Backend_Action_Connection_RenewToken', Backend\Action\Connection\RenewToken::class);
        $bag->addCronjob('backend', 'Execute_Async', '* * * * *', 'Backend_Action_Action_Async');
        $bag->addCronjob('backend', 'Dispatch_Event', '* * * * *', 'Backend_Action_Event_Execute');
        $bag->addCronjob('backend', 'Renew_Token', '0 * * * *', 'Backend_Action_Connection_RenewToken');
        $bag->addRoleScope('Administrator', 'authorization');
        $bag->addRoleScope('Administrator', 'backend');
        $bag->addRoleScope('Administrator', 'consumer');
        $bag->addRoleScope('Administrator', 'default');
        $bag->addRoleScope('Backend', 'authorization');
        $bag->addRoleScope('Backend', 'backend');
        $bag->addRoleScope('Backend', 'default');
        $bag->addRoleScope('Consumer', 'authorization');
        $bag->addRoleScope('Consumer', 'consumer');
        $bag->addRoleScope('Consumer', 'default');
        $bag->addSchema('default', 'Passthru', Passthru::class);
        $bag->addUserScope('Administrator', 'backend');
        $bag->addUserScope('Administrator', 'consumer');
        $bag->addUserScope('Administrator', 'authorization');
        $bag->addUserScope('Administrator', 'default');
        $bag->addPage('Overview', 'overview', self::readFile('overview.html'), Table\Page::STATUS_INVISIBLE);
        $bag->addPage('Getting started', 'getting-started', self::readFile('getting-started.html'));
        $bag->addPage('API', 'api', self::readFile('api.html'));
        $bag->addPage('Authorization', 'authorization', self::readFile('authorization.html'));
        $bag->addPage('Support', 'support', self::readFile('support.html'));
        $bag->addPage('SDK', 'sdk', self::readFile('sdk.html'));

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
                'account.get' => new Operation(
                    action: Backend\Action\Account\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/account',
                    httpCode: 200,
                    return: Model\Backend\User::class,
                ),
                'account.update' => new Operation(
                    action: Backend\Action\Account\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/account',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\UserUpdate::class],
                ),
                'account.changePassword' => new Operation(
                    action: Backend\Action\Account\ChangePassword::class,
                    httpMethod: 'PUT',
                    httpPath: '/account/change_password',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\AccountChangePassword::class],
                ),
                'action.getAll' => new Operation(
                    action: Backend\Action\Action\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/action',
                    httpCode: 200,
                    return: Model\Backend\ActionCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'categoryId' => TypeFactory::getInteger()],
                ),
                'action.create' => new Operation(
                    action: Backend\Action\Action\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/action',
                    httpCode: 201,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\ActionCreate::class],
                ),
                'action.getClasses' => new Operation(
                    action: Backend\Action\Action\GetIndex::class,
                    httpMethod: 'GET',
                    httpPath: '/action/list',
                    httpCode: 200,
                    return: Model\Backend\ActionIndex::class,
                ),
                'action.getForm' => new Operation(
                    action: Backend\Action\Action\GetForm::class,
                    httpMethod: 'GET',
                    httpPath: '/action/form',
                    httpCode: 200,
                    return: Model\FormContainer::class,
                    arguments: ['class' => TypeFactory::getString()],
                ),
                'action.execute' => new Operation(
                    action: Backend\Action\Action\Execute::class,
                    httpMethod: 'POST',
                    httpPath: '/action/execute/:action_id',
                    httpCode: 200,
                    return: Model\Backend\ActionExecuteResponse::class,
                    arguments: ['payload' => Model\Backend\ActionExecuteRequest::class],
                ),
                'action.get' => new Operation(
                    action: Backend\Action\Action\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/action/$action_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Model\Backend\Action::class,
                ),
                'action.update' => new Operation(
                    action: Backend\Action\Action\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/action/$action_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\ActionUpdate::class],
                ),
                'action.delete' => new Operation(
                    action: Backend\Action\Action\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/action/$action_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                ),
                'app.getAllTokens' => new Operation(
                    action: Backend\Action\App\Token\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/app/token',
                    httpCode: 200,
                    return: Model\Backend\AppTokenCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'status' => TypeFactory::getInteger(), 'scope' => TypeFactory::getString(), 'ip' => TypeFactory::getString()],
                ),
                'app.getToken' => new Operation(
                    action: Backend\Action\App\Token\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/app/token/$token_id<[0-9]+>',
                    httpCode: 200,
                    return: Model\Backend\AppToken::class,
                ),
                'app.getAll' => new Operation(
                    action: Backend\Action\App\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/app',
                    httpCode: 200,
                    return: Model\Backend\AppCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'app.create' => new Operation(
                    action: Backend\Action\App\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/app',
                    httpCode: 201,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\AppCreate::class],
                ),
                'app.get' => new Operation(
                    action: Backend\Action\App\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/app/$app_id<[0-9]+>',
                    httpCode: 200,
                    return: Model\Backend\App::class,
                ),
                'app.update' => new Operation(
                    action: Backend\Action\App\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/app/$app_id<[0-9]+>',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\AppUpdate::class],
                ),
                'app.delete' => new Operation(
                    action: Backend\Action\App\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/app/$app_id<[0-9]+>',
                    httpCode: 200,
                    return: Message::class,
                ),
                'app.deleteToken' => new Operation(
                    action: Backend\Action\App\DeleteToken::class,
                    httpMethod: 'DELETE',
                    httpPath: '/app/$app_id<[0-9]+>/token/:token_id',
                    httpCode: 200,
                    return: Message::class,
                ),
                'audit.getAll' => new Operation(
                    action: Backend\Action\Audit\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/audit',
                    httpCode: 200,
                    return: Model\Backend\AuditCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'event' => TypeFactory::getString(), 'ip' => TypeFactory::getString(), 'message' => TypeFactory::getString()],
                ),
                'audit.get' => new Operation(
                    action: Backend\Action\Audit\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/audit/$audit_id<[0-9]+>',
                    httpCode: 200,
                    return: Model\Backend\Audit::class,
                ),
                'category.getAll' => new Operation(
                    action: Backend\Action\Category\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/category',
                    httpCode: 200,
                    return: Model\Backend\CategoryCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'category.insert' => new Operation(
                    action: Backend\Action\Category\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/category',
                    httpCode: 201,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\CategoryCreate::class],
                ),
                'category.get' => new Operation(
                    action: Backend\Action\Category\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/category/$category_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Model\Backend\Category::class,
                ),
                'category.update' => new Operation(
                    action: Backend\Action\Category\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/category/$category_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\CategoryUpdate::class]
                ),
                'category.delete' => new Operation(
                    action: Backend\Action\Category\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/category/$category_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                ),
                'config.getAll' => new Operation(
                    action: Backend\Action\Config\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/config',
                    httpCode: 200,
                    return: Model\Backend\ConfigCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'config.get' => new Operation(
                    action: Backend\Action\Config\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/config/$config_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Model\Backend\Config::class,
                ),
                'config.update' => new Operation(
                    action: Backend\Action\Config\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/config/$config_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\ConfigUpdate::class]
                ),
                'connection.getAll' => new Operation(
                    action: Backend\Action\Connection\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/connection',
                    httpCode: 200,
                    return: Model\Backend\ConnectionCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'connection.create' => new Operation(
                    action: Backend\Action\Connection\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/connection',
                    httpCode: 201,
                    return: Model\Backend\ConnectionCollection::class,
                    arguments: ['payload' => Model\Backend\ConnectionCreate::class],
                ),
                'connection.getClasses' => new Operation(
                    action: Backend\Action\Connection\GetIndex::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/list',
                    httpCode: 200,
                    return: Model\Backend\ConnectionIndex::class,
                ),
                'connection.getForm' => new Operation(
                    action: Backend\Action\Connection\GetForm::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/form',
                    httpCode: 200,
                    return: Model\FormContainer::class,
                    arguments: ['class' => TypeFactory::getString()],
                ),
                'connection.get' => new Operation(
                    action: Backend\Action\Connection\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Model\Backend\Connection::class,
                ),
                'connection.update' => new Operation(
                    action: Backend\Action\Connection\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\ConnectionUpdate::class]
                ),
                'connection.delete' => new Operation(
                    action: Backend\Action\Connection\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                ),
                'connection.getRedirect' => new Operation(
                    action: Backend\Action\Connection\GetRedirect::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>/redirect',
                    httpCode: 200,
                    return: Message::class,
                ),
                'connection.getIntrospection' => new Operation(
                    action: Backend\Action\Connection\Introspection\GetEntities::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>/introspection',
                    httpCode: 200,
                    return: Model\Backend\ConnectionIntrospectionEntities::class,
                ),
                'connection.getIntrospectionForEntity' => new Operation(
                    action: Backend\Action\Connection\Introspection\GetEntity::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>/introspection/:entity',
                    httpCode: 200,
                    return: Model\Backend\ConnectionIntrospectionEntity::class,
                ),
                'cronjob.getAll' => new Operation(
                    action: Backend\Action\Cronjob\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/cronjob',
                    httpCode: 200,
                    return: Model\Backend\CronjobCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'cronjob.create' => new Operation(
                    action: Backend\Action\Cronjob\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/cronjob',
                    httpCode: 201,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\CronjobCreate::class],
                ),
                'cronjob.get' => new Operation(
                    action: Backend\Action\Cronjob\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/cronjob/$cronjob_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Model\Backend\Cronjob::class,
                ),
                'cronjob.update' => new Operation(
                    action: Backend\Action\Cronjob\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/cronjob/$cronjob_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\Cronjob::class],
                ),
                'cronjob.delete' => new Operation(
                    action: Backend\Action\Cronjob\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/cronjob/$cronjob_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                ),
                'dashboard.getAll' => new Operation(
                    action: Backend\Action\Dashboard\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/dashboard',
                    httpCode: 200,
                    return: Model\Backend\Dashboard::class,
                ),
                'event.getAllSubscriptions' => new Operation(
                    action: Backend\Action\Event\Subscription\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/event/subscription',
                    httpCode: 200,
                    return: Model\Backend\EventSubscriptionCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'event.createSubscription' => new Operation(
                    action: Backend\Action\Event\Subscription\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/event/subscription',
                    httpCode: 201,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\EventSubscriptionCreate::class],
                ),
                'event.getSubscription' => new Operation(
                    action: Backend\Action\Event\Subscription\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/event/subscription/$subscription_id<[0-9]+>',
                    httpCode: 200,
                    return: Model\Backend\EventSubscription::class,
                ),
                'event.updateSubscription' => new Operation(
                    action: Backend\Action\Event\Subscription\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/event/subscription/$subscription_id<[0-9]+>',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\EventSubscriptionUpdate::class],
                ),
                'event.deleteSubscription' => new Operation(
                    action: Backend\Action\Event\Subscription\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/event/subscription/$subscription_id<[0-9]+>',
                    httpCode: 200,
                    return: Message::class,
                ),
                'event.getAll' => new Operation(
                    action: Backend\Action\Event\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/event',
                    httpCode: 200,
                    return: Model\Backend\EventCollection::class,
                    arguments: ['categoryId' => TypeFactory::getInteger(), 'startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'event.create' => new Operation(
                    action: Backend\Action\Event\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/event',
                    httpCode: 201,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\EventCreate::class],
                ),
                'event.get' => new Operation(
                    action: Backend\Action\Event\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/event/$event_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Model\Backend\Event::class,
                ),
                'event.update' => new Operation(
                    action: Backend\Action\Event\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/event/$event_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\EventUpdate::class],
                ),
                'event.delete' => new Operation(
                    action: Backend\Action\Event\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/event/$event_id<[0-9]+|^~>',
                    httpCode: 200,
                    return: Message::class,
                ),
                'generator.getProviders' => new Operation(
                    action: Backend\Action\Generator\Index::class,
                    httpMethod: 'GET',
                    httpPath: '/generator',
                    httpCode: 200,
                    return: Model\Backend\GeneratorIndexProviders::class,
                ),
                'generator.getProviderForm' => new Operation(
                    action: Backend\Action\Generator\Form::class,
                    httpMethod: 'GET',
                    httpPath: '/generator/:provider',
                    httpCode: 200,
                    return: Model\FormContainer::class,
                ),
                'generator.executeProvider' => new Operation(
                    action: Backend\Action\Generator\Create::class,
                    httpMethod: 'GET',
                    httpPath: '/generator/:provider',
                    httpCode: 201,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\GeneratorProvider::class],
                ),
                'generator.getChangelog' => new Operation(
                    action: Backend\Action\Generator\Changelog::class,
                    httpMethod: 'PUT',
                    httpPath: '/generator/:provider',
                    httpCode: 200,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\GeneratorProviderChangelog::class],
                ),
                'log.getAllErrors' => new Operation(
                    action: Backend\Action\Log\Error\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/log/error',
                    httpCode: 200,
                    return: Model\Backend\LogErrorCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'log.getError' => new Operation(
                    action: Backend\Action\Log\Error\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/log/error',
                    httpCode: 200,
                    return: Model\Backend\LogError::class,
                ),
                'log.getAll' => new Operation(
                    action: Backend\Action\Log\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/log',
                    httpCode: 200,
                    return: Model\Backend\LogCollection::class,
                    arguments: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'bofy' => TypeFactory::getString()],
                ),
                'log.get' => new Operation(
                    action: Backend\Action\Log\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/log/$log_id<[0-9]+>',
                    httpCode: 200,
                    return: Model\Backend\Log::class,
                ),
                'marketplace.getAll' => new Operation(
                    action: Backend\Action\Marketplace\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/marketplace',
                    httpCode: 200,
                    return: Model\Backend\MarketplaceCollection::class,
                ),
                'marketplace.install' => new Operation(
                    action: Backend\Action\Marketplace\Install::class,
                    httpMethod: 'POST',
                    httpPath: '/marketplace',
                    httpCode: 201,
                    return: Message::class,
                    arguments: ['payload' => Model\Backend\MarketplaceInstall::class],
                ),
                'marketplace.get' => new Operation(
                    action: Backend\Action\Marketplace\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/marketplace/:app_name',
                    httpCode: 200,
                    return: Model\Backend\MarketplaceLocalApp::class,
                ),
                'marketplace.update' => new Operation(
                    action: Backend\Action\Marketplace\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/marketplace/:app_name',
                    httpCode: 200,
                    return: Message::class,
                ),
                'marketplace.remove' => new Operation(
                    action: Backend\Action\Marketplace\Remove::class,
                    httpMethod: 'DELETE',
                    httpPath: '/marketplace/:app_name',
                    httpCode: 200,
                    return: Message::class,
                ),


                '/page' => [
                    'GET' => new Method(Backend\Action\Page\GetAll::class, null, [200 => Model\Backend\PageCollection::class], Model\CollectionQuery::class, 'backend.page'),
                    'POST' => new Method(Backend\Action\Page\Create::class, Model\Backend\PageCreate::class, [201 => Message::class], null, 'backend.page', 'fusio.page.create'),
                ],
                '/page/$page_id<[0-9]+|^~>' => [
                    'GET' => new Method(Backend\Action\Page\Get::class, null, [200 => Model\Backend\Page::class], null, 'backend.page'),
                    'PUT' => new Method(Backend\Action\Page\Update::class, Model\Backend\PageUpdate::class, [200 => Message::class], null, 'backend.page', 'fusio.page.update'),
                    'DELETE' => new Method(Backend\Action\Page\Delete::class, null, [200 => Message::class], null, 'backend.page', 'fusio.page.delete'),
                ],
                '/plan' => [
                    'GET' => new Method(Backend\Action\Plan\GetAll::class, null, [200 => Model\Backend\PlanCollection::class], Model\CollectionQuery::class, 'backend.plan'),
                    'POST' => new Method(Backend\Action\Plan\Create::class, Model\Backend\PlanCreate::class, [201 => Message::class], null, 'backend.plan', 'fusio.plan.create'),
                ],
                '/plan/$plan_id<[0-9]+|^~>' => [
                    'GET' => new Method(Backend\Action\Plan\Get::class, null, [200 => Model\Backend\Plan::class], null, 'backend.plan'),
                    'PUT' => new Method(Backend\Action\Plan\Update::class, Model\Backend\PlanUpdate::class, [200 => Message::class], null, 'backend.plan', 'fusio.plan.update'),
                    'DELETE' => new Method(Backend\Action\Plan\Delete::class, null, [200 => Message::class], null, 'backend.plan', 'fusio.plan.delete'),
                ],
                '/rate' => [
                    'GET' => new Method(Backend\Action\Rate\GetAll::class, null, [200 => Model\Backend\RateCollection::class], Model\CollectionQuery::class, 'backend.rate'),
                    'POST' => new Method(Backend\Action\Rate\Create::class, Model\Backend\RateCreate::class, [201 => Message::class], null, 'backend.rate', 'fusio.rate.create'),
                ],
                '/rate/$rate_id<[0-9]+|^~>' => [
                    'GET' => new Method(Backend\Action\Rate\Get::class, null, [200 => Model\Backend\Rate::class], null, 'backend.rate'),
                    'PUT' => new Method(Backend\Action\Rate\Update::class, Model\Backend\RateUpdate::class, [200 => Message::class], null, 'backend.rate', 'fusio.rate.update'),
                    'DELETE' => new Method(Backend\Action\Rate\Delete::class, null, [200 => Message::class], null, 'backend.rate', 'fusio.rate.delete'),
                ],
                '/role' => [
                    'GET' => new Method(Backend\Action\Role\GetAll::class, null, [200 => Model\Backend\RoleCollection::class], Model\CollectionQuery::class, 'backend.role'),
                    'POST' => new Method(Backend\Action\Role\Create::class, Model\Backend\RoleCreate::class, [201 => Message::class], null, 'backend.role', 'fusio.role.create'),
                ],
                '/role/$role_id<[0-9]+|^~>' => [
                    'GET' => new Method(Backend\Action\Role\Get::class, null, [200 => Model\Backend\Role::class], null, 'backend.role'),
                    'PUT' => new Method(Backend\Action\Role\Update::class, Model\Backend\RoleUpdate::class, [200 => Message::class], null, 'backend.role', 'fusio.role.update'),
                    'DELETE' => new Method(Backend\Action\Role\Delete::class, null, [200 => Message::class], null, 'backend.role', 'fusio.role.delete'),
                ],
                '/routes' => [
                    'GET' => new Method(Backend\Action\Route\GetAll::class, null, [200 => Model\Backend\RouteCollection::class], Model\CollectionCategoryQuery::class, 'backend.route'),
                    'POST' => new Method(Backend\Action\Route\Create::class, Model\Backend\RouteCreate::class, [201 => Message::class], null, 'backend.route', 'fusio.route.create'),
                ],
                '/routes/$route_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Route\Get::class, null, [200 => Model\Backend\Route::class], null, 'backend.route'),
                    'PUT' => new Method(Backend\Action\Route\Update::class, Model\Backend\RouteUpdate::class, [200 => Message::class], null, 'backend.route', 'fusio.route.update'),
                    'DELETE' => new Method(Backend\Action\Route\Delete::class, null, [200 => Message::class], null, 'backend.route', 'fusio.route.delete'),
                ],
                '/schema' => [
                    'GET' => new Method(Backend\Action\Schema\GetAll::class, null, [200 => Model\Backend\SchemaCollection::class], Model\CollectionCategoryQuery::class, 'backend.schema'),
                    'POST' => new Method(Backend\Action\Schema\Create::class, Model\Backend\SchemaCreate::class, [201 => Message::class], null, 'backend.schema', 'fusio.schema.create'),
                ],
                '/schema/preview/:schema_id' => [
                    'POST' => new Method(Backend\Action\Schema\GetPreview::class, null, [200 => Model\Backend\SchemaPreviewResponse::class], null, 'backend.schema'),
                ],
                '/schema/form/$schema_id<[0-9]+>' => [
                    'PUT' => new Method(Backend\Action\Schema\Form::class, Model\Backend\SchemaForm::class, [200 => Message::class], null, 'backend.schema'),
                ],
                '/schema/$schema_id<[0-9]+|^~>' => [
                    'GET' => new Method(Backend\Action\Schema\Get::class, null, [200 => Model\Backend\Schema::class], null, 'backend.schema'),
                    'PUT' => new Method(Backend\Action\Schema\Update::class, Model\Backend\SchemaUpdate::class, [200 => Message::class], null, 'backend.schema', 'fusio.schema.update'),
                    'DELETE' => new Method(Backend\Action\Schema\Delete::class, null, [200 => Message::class], null, 'backend.schema', 'fusio.schema.delete'),
                ],
                '/scope' => [
                    'GET' => new Method(Backend\Action\Scope\GetAll::class, null, [200 => Model\Backend\ScopeCollection::class], Model\CollectionCategoryQuery::class, 'backend.scope'),
                    'POST' => new Method(Backend\Action\Scope\Create::class, Model\Backend\ScopeCreate::class, [201 => Message::class], null, 'backend.scope', 'fusio.scope.create'),
                ],
                '/scope/categories' => [
                    'GET' => new Method(Backend\Action\Scope\GetCategories::class, null, [200 => Model\Backend\ScopeCategories::class], null, 'backend.scope'),
                ],
                '/scope/$scope_id<[0-9]+|^~>' => [
                    'GET' => new Method(Backend\Action\Scope\Get::class, null, [200 => Model\Backend\Scope::class], null, 'backend.scope'),
                    'PUT' => new Method(Backend\Action\Scope\Update::class, Model\Backend\ScopeUpdate::class, [200 => Message::class], null, 'backend.scope', 'fusio.scope.update'),
                    'DELETE' => new Method(Backend\Action\Scope\Delete::class, null, [200 => Message::class], null, 'backend.scope', 'fusio.scope.delete'),
                ],
                '/sdk' => [
                    'GET' => new Method(Backend\Action\Sdk\GetAll::class, null, [200 => Model\Backend\SdkResponse::class], null, 'backend.sdk'),
                    'POST' => new Method(Backend\Action\Sdk\Generate::class, Model\Backend\SdkGenerate::class, [200 => Message::class], null, 'backend.sdk'),
                ],
                '/statistic/count_requests' => [
                    'GET' => new Method(Backend\Action\Statistic\GetCountRequests::class, null, [200 => Model\Backend\StatisticCount::class], Model\Backend\LogCollectionQuery::class, 'backend.statistic'),
                ],
                '/statistic/errors_per_route' => [
                    'GET' => new Method(Backend\Action\Statistic\GetErrorsPerRoute::class, null, [200 => Model\Backend\StatisticChart::class], Model\Backend\LogCollectionQuery::class, 'backend.statistic'),
                ],
                '/statistic/incoming_requests' => [
                    'GET' => new Method(Backend\Action\Statistic\GetIncomingRequests::class, null, [200 => Model\Backend\StatisticChart::class], Model\Backend\LogCollectionQuery::class, 'backend.statistic'),
                ],
                '/statistic/incoming_transactions' => [
                    'GET' => new Method(Backend\Action\Statistic\GetIncomingTransactions::class, null, [200 => Model\Backend\StatisticChart::class], Model\Backend\TransactionCollectionQuery::class, 'backend.statistic'),
                ],
                '/statistic/issued_tokens' => [
                    'GET' => new Method(Backend\Action\Statistic\GetIssuedTokens::class, null, [200 => Model\Backend\StatisticChart::class], Model\Backend\AppTokenCollectionQuery::class, 'backend.statistic'),
                ],
                '/statistic/most_used_apps' => [
                    'GET' => new Method(Backend\Action\Statistic\GetMostUsedApps::class, null, [200 => Model\Backend\StatisticChart::class], Model\Backend\LogCollectionQuery::class, 'backend.statistic'),
                ],
                '/statistic/most_used_routes' => [
                    'GET' => new Method(Backend\Action\Statistic\GetMostUsedRoutes::class, null, [200 => Model\Backend\StatisticChart::class], Model\Backend\LogCollectionQuery::class, 'backend.statistic'),
                ],
                '/statistic/time_average' => [
                    'GET' => new Method(Backend\Action\Statistic\GetTimeAverage::class, null, [200 => Model\Backend\StatisticChart::class], Model\Backend\LogCollectionQuery::class, 'backend.statistic'),
                ],
                '/statistic/time_per_route' => [
                    'GET' => new Method(Backend\Action\Statistic\GetTimePerRoute::class, null, [200 => Model\Backend\StatisticChart::class], Model\Backend\LogCollectionQuery::class, 'backend.statistic'),
                ],
                '/statistic/used_points' => [
                    'GET' => new Method(Backend\Action\Statistic\GetUsedPoints::class, null, [200 => Model\Backend\StatisticChart::class], Model\Backend\PlanUsageCollectionQuery::class, 'backend.statistic'),
                ],
                '/transaction' => [
                    'GET' => new Method(Backend\Action\Transaction\GetAll::class, null, [200 => Model\Backend\TransactionCollection::class], Model\Backend\TransactionCollectionQuery::class, 'backend.transaction'),
                ],
                '/transaction/$transaction_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\Transaction\Get::class, null, [200 => Model\Backend\Transaction::class], null, 'backend.transaction'),
                ],
                '/trash' => [
                    'GET' => new Method(Backend\Action\Trash\GetTypes::class, null, [200 => Model\Backend\TrashTypes::class], null, 'backend.trash'),
                ],
                '/trash/:type' => [
                    'GET' => new Method(Backend\Action\Trash\GetAll::class, null, [200 => Model\Backend\TrashDataCollection::class], Model\CollectionQuery::class, 'backend.trash'),
                    'POST' => new Method(Backend\Action\Trash\Restore::class, Model\Backend\TrashRestore::class, [200 => Message::class], null, 'backend.trash'),
                ],
                '/user' => [
                    'GET' => new Method(Backend\Action\User\GetAll::class, null, [200 => Model\Backend\UserCollection::class], Model\CollectionQuery::class, 'backend.user'),
                    'POST' => new Method(Backend\Action\User\Create::class, Model\Backend\UserCreate::class, [201 => Message::class], null, 'backend.user', 'fusio.user.create'),
                ],
                '/user/$user_id<[0-9]+>' => [
                    'GET' => new Method(Backend\Action\User\Get::class, null, [200 => Model\Backend\User::class], null, 'backend.user'),
                    'PUT' => new Method(Backend\Action\User\Update::class, Model\Backend\UserUpdate::class, [200 => Message::class], null, 'backend.user', 'fusio.user.update'),
                    'DELETE' => new Method(Backend\Action\User\Delete::class, null, [200 => Message::class], null, 'backend.user', 'fusio.user.delete'),
                ],
            ],
            'consumer' => [
                '/app' => [
                    'GET' => new Method(Consumer\Action\App\GetAll::class, null, [200 => Model\Consumer\AppCollection::class], Model\CollectionQuery::class, 'consumer.app'),
                    'POST' => new Method(Consumer\Action\App\Create::class, Model\Consumer\AppCreate::class, [201 => Message::class], null, 'consumer.app'),
                ],
                '/app/$app_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\App\Get::class, null, [200 => Model\Consumer\App::class], null, 'consumer.app'),
                    'PUT' => new Method(Consumer\Action\App\Update::class, Model\Consumer\AppUpdate::class, [200 => Message::class], null, 'consumer.app'),
                    'DELETE' => new Method(Consumer\Action\App\Delete::class, null, [200 => Message::class], null, 'consumer.app'),
                ],
                '/event' => [
                    'GET' => new Method(Consumer\Action\Event\GetAll::class, null, [200 => Model\Consumer\EventCollection::class], Model\CollectionQuery::class, 'consumer.event'),
                ],
                '/grant' => [
                    'GET' => new Method(Consumer\Action\Grant\GetAll::class, null, [200 => Model\Consumer\GrantCollection::class], Model\CollectionQuery::class, 'consumer.grant'),
                ],
                '/grant/$grant_id<[0-9]+>' => [
                    'DELETE' => new Method(Consumer\Action\Grant\Delete::class, null, [204 => Message::class], null, 'consumer.grant'),
                ],
                '/log' => [
                    'GET' => new Method(Consumer\Action\Log\GetAll::class, null, [200 => Model\Consumer\LogCollection::class], Model\CollectionQuery::class, 'consumer.log'),
                ],
                '/log/$log_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Log\Get::class, null, [200 => Model\Consumer\Log::class], Model\CollectionQuery::class, 'consumer.log'),
                ],
                '/page' => [
                    'GET' => new Method(Consumer\Action\Page\GetAll::class, null, [200 => Model\Consumer\PageCollection::class], Model\CollectionQuery::class, 'consumer.page', null, true),
                ],
                '/page/:page_id' => [
                    'GET' => new Method(Consumer\Action\Page\Get::class, null, [200 => Model\Consumer\Page::class], null, 'consumer.page', null, true),
                ],
                '/payment/:provider/portal' => [
                    'POST' => new Method(Consumer\Action\Payment\Portal::class, Model\Consumer\PaymentPortalRequest::class, [200 => Model\Consumer\PaymentPortalResponse::class], null, 'consumer.payment'),
                ],
                '/payment/:provider/checkout' => [
                    'POST' => new Method(Consumer\Action\Payment\Checkout::class, Model\Consumer\PaymentCheckoutRequest::class, [200 => Model\Consumer\PaymentCheckoutResponse::class], null, 'consumer.payment'),
                ],
                '/plan' => [
                    'GET' => new Method(Consumer\Action\Plan\GetAll::class, null, [200 => Model\Consumer\PlanCollection::class], Model\CollectionQuery::class, 'consumer.plan'),
                ],
                '/plan/$plan_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Plan\Get::class, null, [200 => Model\Consumer\Plan::class], null, 'consumer.plan'),
                ],
                '/scope' => [
                    'GET' => new Method(Consumer\Action\Scope\GetAll::class, null, [200 => Model\Consumer\ScopeCollection::class], Model\CollectionQuery::class, 'consumer.scope'),
                ],
                '/subscription' => [
                    'GET' => new Method(Consumer\Action\Event\Subscription\GetAll::class, null, [200 => Model\Consumer\EventSubscriptionCollection::class], Model\CollectionQuery::class, 'consumer.subscription'),
                    'POST' => new Method(Consumer\Action\Event\Subscription\Create::class, Model\Consumer\EventSubscriptionCreate::class, [201 => Message::class], null, 'consumer.subscription'),
                ],
                '/subscription/$subscription_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Event\Subscription\Get::class, null, [200 => Model\Consumer\EventSubscription::class], null, 'consumer.subscription'),
                    'PUT' => new Method(Consumer\Action\Event\Subscription\Update::class, Model\Consumer\EventSubscriptionUpdate::class, [200 => Message::class], null, 'consumer.subscription'),
                    'DELETE' => new Method(Consumer\Action\Event\Subscription\Delete::class, null, [200 => Message::class], null, 'consumer.subscription'),
                ],
                '/transaction' => [
                    'GET' => new Method(Consumer\Action\Transaction\GetAll::class, null, [200 => Model\Consumer\TransactionCollection::class], Model\CollectionQuery::class, 'consumer.transaction'),
                ],
                '/transaction/$transaction_id<[0-9]+>' => [
                    'GET' => new Method(Consumer\Action\Transaction\Get::class, null, [200 => Model\Consumer\Transaction::class], null, 'consumer.transaction'),
                ],
                '/account' => [
                    'GET' => new Method(Consumer\Action\User\Get::class, null, [200 => Model\Consumer\UserAccount::class], null, 'consumer.user'),
                    'PUT' => new Method(Consumer\Action\User\Update::class, Model\Consumer\UserAccount::class, [200 => Message::class], null, 'consumer.user'),
                ],
                '/account/change_password' => [
                    'PUT' => new Method(Consumer\Action\User\ChangePassword::class, Model\Backend\AccountChangePassword::class, [200 => Message::class], null, 'consumer.user'),
                ],
                '/activate' => [
                    'POST' => new Method(Consumer\Action\User\Activate::class, Model\Consumer\UserActivate::class, [200 => Message::class], null, 'consumer.user', null, true),
                ],
                '/authorize' => [
                    'GET' => new Method(Consumer\Action\User\GetApp::class, null, [200 => Model\Consumer\AuthorizeMeta::class], null, 'consumer.user', null, true),
                    'POST' => new Method(Consumer\Action\User\Authorize::class, Model\Consumer\AuthorizeRequest::class, [200 => Model\Consumer\AuthorizeResponse::class], null, 'consumer.user', null, true),
                ],
                '/login' => [
                    'POST' => new Method(Consumer\Action\User\Login::class, Model\Consumer\UserLogin::class, [200 => Model\Consumer\UserJWT::class], null, 'consumer.user', null, true),
                    'PUT' => new Method(Consumer\Action\User\Refresh::class, Model\Consumer\UserRefresh::class, [200 => Model\Consumer\UserJWT::class], null, 'consumer.user', null, true),
                ],
                '/provider/:provider' => [
                    'POST' => new Method(Consumer\Action\User\Provider::class, Model\Consumer\UserProvider::class, [200 => Model\Consumer\UserJWT::class], null, 'consumer.user', null, true),
                ],
                '/register' => [
                    'POST' => new Method(Consumer\Action\User\Register::class, Model\Consumer\UserRegister::class, [200 => Message::class], null, 'consumer.user', null, true),
                ],
                '/password_reset' => [
                    'POST' => new Method(Consumer\Action\User\ResetPassword\Request::class, Model\Consumer\UserEmail::class, [200 => Message::class], null, 'consumer.user', null, true),
                    'PUT' => new Method(Consumer\Action\User\ResetPassword\Execute::class, Model\Consumer\UserPasswordReset::class, [200 => Message::class], null, 'consumer.user', null, true),
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
                    'GET' => new Method(System\Action\GetHealth::class, null, [200 => Model\System\HealthCheck::class], null, null, null, true),
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
                '/connection/:name/callback' => [
                    'GET' => new Method(System\Action\ConnectionCallback::class, null, [200 => Message::class], null, null, null, true),
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

    /**
     * Reads files in new line neutral way that means we always use \n
     */
    private static function readFile(string $file): string
    {
        $lines = file(__DIR__ . '/resources/' . $file);
        $lines = array_map('trim', $lines);
        return implode("\n", $lines);
    }
}

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

        foreach (self::getOperations() as $category => $operations) {
            $bag->addOperations($category, $operations);
        }

        return self::$data = $bag;
    }

    private static function getOperations(): array
    {
        return [
            'default' => [
                'getAbout' => new Operation(
                    action: System\Action\GetAbout::class,
                    httpMethod: 'GET',
                    httpPath: '/',
                    httpCode: 200,
                    outgoing: Model\System\About::class,
                    public: true
                ),
            ],
            'backend' => [
                'account.get' => new Operation(
                    action: Backend\Action\Account\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/account',
                    httpCode: 200,
                    outgoing: Model\Backend\User::class,
                ),
                'account.update' => new Operation(
                    action: Backend\Action\Account\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/account',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\UserUpdate::class,
                ),
                'account.changePassword' => new Operation(
                    action: Backend\Action\Account\ChangePassword::class,
                    httpMethod: 'PUT',
                    httpPath: '/account/change_password',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\AccountChangePassword::class,
                ),
                'action.getAll' => new Operation(
                    action: Backend\Action\Action\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/action',
                    httpCode: 200,
                    outgoing: Model\Backend\ActionCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'categoryId' => TypeFactory::getInteger()],
                ),
                'action.create' => new Operation(
                    action: Backend\Action\Action\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/action',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\ActionCreate::class,
                ),
                'action.getClasses' => new Operation(
                    action: Backend\Action\Action\GetIndex::class,
                    httpMethod: 'GET',
                    httpPath: '/action/list',
                    httpCode: 200,
                    outgoing: Model\Backend\ActionIndex::class,
                ),
                'action.getForm' => new Operation(
                    action: Backend\Action\Action\GetForm::class,
                    httpMethod: 'GET',
                    httpPath: '/action/form',
                    httpCode: 200,
                    outgoing: Model\FormContainer::class,
                    parameters: ['class' => TypeFactory::getString()],
                ),
                'action.execute' => new Operation(
                    action: Backend\Action\Action\Execute::class,
                    httpMethod: 'POST',
                    httpPath: '/action/execute/:action_id',
                    httpCode: 200,
                    outgoing: Model\Backend\ActionExecuteResponse::class,
                    incoming: Model\Backend\ActionExecuteRequest::class,
                ),
                'action.get' => new Operation(
                    action: Backend\Action\Action\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/action/$action_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Action::class,
                ),
                'action.update' => new Operation(
                    action: Backend\Action\Action\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/action/$action_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\ActionUpdate::class,
                ),
                'action.delete' => new Operation(
                    action: Backend\Action\Action\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/action/$action_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'app.getAllTokens' => new Operation(
                    action: Backend\Action\App\Token\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/app/token',
                    httpCode: 200,
                    outgoing: Model\Backend\AppTokenCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'status' => TypeFactory::getInteger(), 'scope' => TypeFactory::getString(), 'ip' => TypeFactory::getString()],
                ),
                'app.getToken' => new Operation(
                    action: Backend\Action\App\Token\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/app/token/$token_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Backend\AppToken::class,
                ),
                'app.getAll' => new Operation(
                    action: Backend\Action\App\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/app',
                    httpCode: 200,
                    outgoing: Model\Backend\AppCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'app.create' => new Operation(
                    action: Backend\Action\App\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/app',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\AppCreate::class,
                ),
                'app.get' => new Operation(
                    action: Backend\Action\App\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/app/$app_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Backend\App::class,
                ),
                'app.update' => new Operation(
                    action: Backend\Action\App\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/app/$app_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\AppUpdate::class,
                ),
                'app.delete' => new Operation(
                    action: Backend\Action\App\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/app/$app_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'app.deleteToken' => new Operation(
                    action: Backend\Action\App\DeleteToken::class,
                    httpMethod: 'DELETE',
                    httpPath: '/app/$app_id<[0-9]+>/token/:token_id',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'audit.getAll' => new Operation(
                    action: Backend\Action\Audit\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/audit',
                    httpCode: 200,
                    outgoing: Model\Backend\AuditCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'event' => TypeFactory::getString(), 'ip' => TypeFactory::getString(), 'message' => TypeFactory::getString()],
                ),
                'audit.get' => new Operation(
                    action: Backend\Action\Audit\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/audit/$audit_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Backend\Audit::class,
                ),
                'category.getAll' => new Operation(
                    action: Backend\Action\Category\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/category',
                    httpCode: 200,
                    outgoing: Model\Backend\CategoryCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'category.insert' => new Operation(
                    action: Backend\Action\Category\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/category',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\CategoryCreate::class,
                ),
                'category.get' => new Operation(
                    action: Backend\Action\Category\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/category/$category_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Category::class,
                ),
                'category.update' => new Operation(
                    action: Backend\Action\Category\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/category/$category_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\CategoryUpdate::class
                ),
                'category.delete' => new Operation(
                    action: Backend\Action\Category\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/category/$category_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'config.getAll' => new Operation(
                    action: Backend\Action\Config\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/config',
                    httpCode: 200,
                    outgoing: Model\Backend\ConfigCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'config.get' => new Operation(
                    action: Backend\Action\Config\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/config/$config_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Config::class,
                ),
                'config.update' => new Operation(
                    action: Backend\Action\Config\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/config/$config_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\ConfigUpdate::class
                ),
                'connection.getAll' => new Operation(
                    action: Backend\Action\Connection\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/connection',
                    httpCode: 200,
                    outgoing: Model\Backend\ConnectionCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'connection.create' => new Operation(
                    action: Backend\Action\Connection\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/connection',
                    httpCode: 201,
                    outgoing: Model\Backend\ConnectionCollection::class,
                    incoming: Model\Backend\ConnectionCreate::class,
                ),
                'connection.getClasses' => new Operation(
                    action: Backend\Action\Connection\GetIndex::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/list',
                    httpCode: 200,
                    outgoing: Model\Backend\ConnectionIndex::class,
                ),
                'connection.getForm' => new Operation(
                    action: Backend\Action\Connection\GetForm::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/form',
                    httpCode: 200,
                    outgoing: Model\FormContainer::class,
                    parameters: ['class' => TypeFactory::getString()],
                ),
                'connection.get' => new Operation(
                    action: Backend\Action\Connection\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Connection::class,
                ),
                'connection.update' => new Operation(
                    action: Backend\Action\Connection\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\ConnectionUpdate::class
                ),
                'connection.delete' => new Operation(
                    action: Backend\Action\Connection\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'connection.getRedirect' => new Operation(
                    action: Backend\Action\Connection\GetRedirect::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>/redirect',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'connection.getIntrospection' => new Operation(
                    action: Backend\Action\Connection\Introspection\GetEntities::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>/introspection',
                    httpCode: 200,
                    outgoing: Model\Backend\ConnectionIntrospectionEntities::class,
                ),
                'connection.getIntrospectionForEntity' => new Operation(
                    action: Backend\Action\Connection\Introspection\GetEntity::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/$connection_id<[0-9]+|^~>/introspection/:entity',
                    httpCode: 200,
                    outgoing: Model\Backend\ConnectionIntrospectionEntity::class,
                ),
                'cronjob.getAll' => new Operation(
                    action: Backend\Action\Cronjob\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/cronjob',
                    httpCode: 200,
                    outgoing: Model\Backend\CronjobCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'cronjob.create' => new Operation(
                    action: Backend\Action\Cronjob\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/cronjob',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\CronjobCreate::class,
                ),
                'cronjob.get' => new Operation(
                    action: Backend\Action\Cronjob\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/cronjob/$cronjob_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Cronjob::class,
                ),
                'cronjob.update' => new Operation(
                    action: Backend\Action\Cronjob\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/cronjob/$cronjob_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\Cronjob::class,
                ),
                'cronjob.delete' => new Operation(
                    action: Backend\Action\Cronjob\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/cronjob/$cronjob_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'dashboard.getAll' => new Operation(
                    action: Backend\Action\Dashboard\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/dashboard',
                    httpCode: 200,
                    outgoing: Model\Backend\Dashboard::class,
                ),
                'event.getAllSubscriptions' => new Operation(
                    action: Backend\Action\Event\Subscription\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/event/subscription',
                    httpCode: 200,
                    outgoing: Model\Backend\EventSubscriptionCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'event.createSubscription' => new Operation(
                    action: Backend\Action\Event\Subscription\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/event/subscription',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\EventSubscriptionCreate::class,
                ),
                'event.getSubscription' => new Operation(
                    action: Backend\Action\Event\Subscription\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/event/subscription/$subscription_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Backend\EventSubscription::class,
                ),
                'event.updateSubscription' => new Operation(
                    action: Backend\Action\Event\Subscription\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/event/subscription/$subscription_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\EventSubscriptionUpdate::class,
                ),
                'event.deleteSubscription' => new Operation(
                    action: Backend\Action\Event\Subscription\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/event/subscription/$subscription_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'event.getAll' => new Operation(
                    action: Backend\Action\Event\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/event',
                    httpCode: 200,
                    outgoing: Model\Backend\EventCollection::class,
                    parameters: ['categoryId' => TypeFactory::getInteger(), 'startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'event.create' => new Operation(
                    action: Backend\Action\Event\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/event',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\EventCreate::class,
                ),
                'event.get' => new Operation(
                    action: Backend\Action\Event\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/event/$event_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Event::class,
                ),
                'event.update' => new Operation(
                    action: Backend\Action\Event\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/event/$event_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\EventUpdate::class,
                ),
                'event.delete' => new Operation(
                    action: Backend\Action\Event\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/event/$event_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'generator.getProviders' => new Operation(
                    action: Backend\Action\Generator\Index::class,
                    httpMethod: 'GET',
                    httpPath: '/generator',
                    httpCode: 200,
                    outgoing: Model\Backend\GeneratorIndexProviders::class,
                ),
                'generator.getProviderForm' => new Operation(
                    action: Backend\Action\Generator\Form::class,
                    httpMethod: 'GET',
                    httpPath: '/generator/:provider',
                    httpCode: 200,
                    outgoing: Model\FormContainer::class,
                ),
                'generator.executeProvider' => new Operation(
                    action: Backend\Action\Generator\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/generator/:provider',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\GeneratorProvider::class,
                ),
                'generator.getChangelog' => new Operation(
                    action: Backend\Action\Generator\Changelog::class,
                    httpMethod: 'PUT',
                    httpPath: '/generator/:provider',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\GeneratorProviderChangelog::class,
                ),
                'log.getAllErrors' => new Operation(
                    action: Backend\Action\Log\Error\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/log/error',
                    httpCode: 200,
                    outgoing: Model\Backend\LogErrorCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'log.getError' => new Operation(
                    action: Backend\Action\Log\Error\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/log/error/$error_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Backend\LogError::class,
                ),
                'log.getAll' => new Operation(
                    action: Backend\Action\Log\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/log',
                    httpCode: 200,
                    outgoing: Model\Backend\LogCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'log.get' => new Operation(
                    action: Backend\Action\Log\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/log/$log_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Backend\Log::class,
                ),
                'marketplace.getAll' => new Operation(
                    action: Backend\Action\Marketplace\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/marketplace',
                    httpCode: 200,
                    outgoing: Model\Backend\MarketplaceCollection::class,
                ),
                'marketplace.install' => new Operation(
                    action: Backend\Action\Marketplace\Install::class,
                    httpMethod: 'POST',
                    httpPath: '/marketplace',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\MarketplaceInstall::class,
                ),
                'marketplace.get' => new Operation(
                    action: Backend\Action\Marketplace\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/marketplace/:app_name',
                    httpCode: 200,
                    outgoing: Model\Backend\MarketplaceLocalApp::class,
                ),
                'marketplace.update' => new Operation(
                    action: Backend\Action\Marketplace\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/marketplace/:app_name',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'marketplace.remove' => new Operation(
                    action: Backend\Action\Marketplace\Remove::class,
                    httpMethod: 'DELETE',
                    httpPath: '/marketplace/:app_name',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'page.getAll' => new Operation(
                    action: Backend\Action\Page\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/page',
                    httpCode: 200,
                    outgoing: Model\Backend\PageCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'page.create' => new Operation(
                    action: Backend\Action\Page\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/page',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\PageCreate::class,
                ),
                'page.get' => new Operation(
                    action: Backend\Action\Page\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/page/$page_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Page::class,
                ),
                'page.update' => new Operation(
                    action: Backend\Action\Page\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/page/$page_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\PageUpdate::class,
                ),
                'page.delete' => new Operation(
                    action: Backend\Action\Page\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/page/$page_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'plan.getAll' => new Operation(
                    action: Backend\Action\Plan\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/plan',
                    httpCode: 200,
                    outgoing: Model\Backend\PlanCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'plan.create' => new Operation(
                    action: Backend\Action\Plan\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/plan',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\PlanCreate::class,
                ),
                'plan.get' => new Operation(
                    action: Backend\Action\Plan\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/plan/$plan_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Plan::class,
                ),
                'plan.update' => new Operation(
                    action: Backend\Action\Plan\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/plan/$plan_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\PlanUpdate::class,
                ),
                'plan.delete' => new Operation(
                    action: Backend\Action\Plan\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/plan/$plan_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'rate.getAll' => new Operation(
                    action: Backend\Action\Rate\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/rate',
                    httpCode: 200,
                    outgoing: Model\Backend\RateCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'rate.create' => new Operation(
                    action: Backend\Action\Rate\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/rate',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\RateCreate::class,
                ),
                'rate.get' => new Operation(
                    action: Backend\Action\Rate\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/rate/$rate_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Rate::class,
                ),
                'rate.update' => new Operation(
                    action: Backend\Action\Rate\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/rate/$rate_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\RateUpdate::class,
                ),
                'rate.delete' => new Operation(
                    action: Backend\Action\Rate\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/rate/$rate_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'role.getAll' => new Operation(
                    action: Backend\Action\Role\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/role',
                    httpCode: 200,
                    outgoing: Model\Backend\RoleCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'role.create' => new Operation(
                    action: Backend\Action\Role\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/role',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\RoleCreate::class,
                ),
                'role.get' => new Operation(
                    action: Backend\Action\Role\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/role/$role_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Role::class,
                ),
                'role.update' => new Operation(
                    action: Backend\Action\Role\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/role/$role_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\RoleUpdate::class,
                ),
                'role.delete' => new Operation(
                    action: Backend\Action\Role\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/role/$role_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'operation.getAll' => new Operation(
                    action: Backend\Action\Operation\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/operation',
                    httpCode: 200,
                    outgoing: Model\Backend\OperationCollection::class,
                    parameters: ['categoryId' => TypeFactory::getInteger(), 'startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'operation.create' => new Operation(
                    action: Backend\Action\Operation\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/operation',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\OperationCreate::class,
                ),
                'operation.get' => new Operation(
                    action: Backend\Action\Operation\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/operation/$operation_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Backend\Operation::class,
                ),
                'operation.update' => new Operation(
                    action: Backend\Action\Operation\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/operation/$operation_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\OperationUpdate::class,
                ),
                'operation.delete' => new Operation(
                    action: Backend\Action\Operation\Update::class,
                    httpMethod: 'DELETE',
                    httpPath: '/operation/$operation_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'schema.getAll' => new Operation(
                    action: Backend\Action\Schema\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/schema',
                    httpCode: 200,
                    outgoing: Model\Backend\SchemaCollection::class,
                    parameters: ['categoryId' => TypeFactory::getInteger(), 'startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'schema.create' => new Operation(
                    action: Backend\Action\Schema\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/schema',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\SchemaCreate::class,
                ),
                'schema.getPreview' => new Operation(
                    action: Backend\Action\Schema\GetPreview::class,
                    httpMethod: 'POST',
                    httpPath: '/schema/preview/:schema_id',
                    httpCode: 200,
                    outgoing: Model\Backend\SchemaPreviewResponse::class,
                ),
                'schema.updateForm' => new Operation(
                    action: Backend\Action\Schema\Form::class,
                    httpMethod: 'PUT',
                    httpPath: '/schema/form/$schema_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\SchemaForm::class,
                ),
                'schema.get' => new Operation(
                    action: Backend\Action\Schema\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/schema/$schema_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Schema::class,
                ),
                'schema.update' => new Operation(
                    action: Backend\Action\Schema\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/schema/$schema_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\SchemaUpdate::class,
                ),
                'schema.delete' => new Operation(
                    action: Backend\Action\Schema\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/schema/$schema_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'scope.getAll' => new Operation(
                    action: Backend\Action\Scope\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/scope',
                    httpCode: 200,
                    outgoing: Model\Backend\ScopeCollection::class,
                    parameters: ['categoryId' => TypeFactory::getInteger(), 'startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'scope.create' => new Operation(
                    action: Backend\Action\Scope\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/scope',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\ScopeCreate::class,
                ),
                'scope.getCategories' => new Operation(
                    action: Backend\Action\Scope\GetCategories::class,
                    httpMethod: 'GET',
                    httpPath: '/scope/categories',
                    httpCode: 200,
                    outgoing: Model\Backend\ScopeCategories::class,
                ),
                'scope.get' => new Operation(
                    action: Backend\Action\Scope\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/scope/$scope_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Model\Backend\Scope::class,
                ),
                'scope.update' => new Operation(
                    action: Backend\Action\Scope\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/scope/$scope_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\ScopeUpdate::class,
                ),
                'scope.delete' => new Operation(
                    action: Backend\Action\Scope\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/scope/$scope_id<[0-9]+|^~>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'sdk.getAll' => new Operation(
                    action: Backend\Action\Sdk\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/sdk',
                    httpCode: 200,
                    outgoing: Model\Backend\SdkResponse::class,
                ),
                'sdk.generate' => new Operation(
                    action: Backend\Action\Sdk\Generate::class,
                    httpMethod: 'POST',
                    httpPath: '/sdk',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\SdkGenerate::class,
                ),
                'statistic.getCountRequests' => new Operation(
                    action: Backend\Action\Statistic\GetCountRequests::class,
                    httpMethod: 'GET',
                    httpPath: '/statistic/count_requests',
                    httpCode: 200,
                    outgoing: Model\Backend\StatisticCount::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'statistic.getErrorsPerRoute' => new Operation(
                    action: Backend\Action\Statistic\GetErrorsPerRoute::class,
                    httpMethod: 'GET',
                    httpPath: '/statistic/errors_per_route',
                    httpCode: 200,
                    outgoing: Model\Backend\StatisticChart::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'statistic.getIncomingRequests' => new Operation(
                    action: Backend\Action\Statistic\GetIncomingRequests::class,
                    httpMethod: 'GET',
                    httpPath: '/statistic/incoming_requests',
                    httpCode: 200,
                    outgoing: Model\Backend\StatisticChart::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'statistic.getIncomingTransactions' => new Operation(
                    action: Backend\Action\Statistic\GetIncomingTransactions::class,
                    httpMethod: 'GET',
                    httpPath: '/statistic/incoming_transactions',
                    httpCode: 200,
                    outgoing: Model\Backend\StatisticChart::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'statistic.getIssuedTokens' => new Operation(
                    action: Backend\Action\Statistic\GetIssuedTokens::class,
                    httpMethod: 'GET',
                    httpPath: '/statistic/issued_tokens',
                    httpCode: 200,
                    outgoing: Model\Backend\StatisticChart::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'statistic.getMostUsedApps' => new Operation(
                    action: Backend\Action\Statistic\GetMostUsedApps::class,
                    httpMethod: 'GET',
                    httpPath: '/statistic/most_used_apps',
                    httpCode: 200,
                    outgoing: Model\Backend\StatisticChart::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'statistic.getMostUsedRoutes' => new Operation(
                    action: Backend\Action\Statistic\GetMostUsedRoutes::class,
                    httpMethod: 'GET',
                    httpPath: '/statistic/most_used_routes',
                    httpCode: 200,
                    outgoing: Model\Backend\StatisticChart::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'statistic.getTimeAverage' => new Operation(
                    action: Backend\Action\Statistic\GetTimeAverage::class,
                    httpMethod: 'GET',
                    httpPath: '/statistic/time_average',
                    httpCode: 200,
                    outgoing: Model\Backend\StatisticChart::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'statistic.getTimePerRoute' => new Operation(
                    action: Backend\Action\Statistic\GetTimePerRoute::class,
                    httpMethod: 'GET',
                    httpPath: '/statistic/time_per_route',
                    httpCode: 200,
                    outgoing: Model\Backend\StatisticChart::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'statistic.getUsedPoints' => new Operation(
                    action: Backend\Action\Statistic\GetUsedPoints::class,
                    httpMethod: 'GET',
                    httpPath: '/statistic/used_points',
                    httpCode: 200,
                    outgoing: Model\Backend\StatisticChart::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'routeId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'ip' => TypeFactory::getString(), 'userAgent' => TypeFactory::getString(), 'method' => TypeFactory::getString(), 'path' => TypeFactory::getString(), 'header' => TypeFactory::getString(), 'body' => TypeFactory::getString()],
                ),
                'transaction.getAll' => new Operation(
                    action: Backend\Action\Transaction\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/transaction',
                    httpCode: 200,
                    outgoing: Model\Backend\TransactionCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString(), 'from' => TypeFactory::getDateTime(), 'to' => TypeFactory::getDateTime(), 'planId' => TypeFactory::getInteger(), 'userId' => TypeFactory::getInteger(), 'appId' => TypeFactory::getInteger(), 'status' => TypeFactory::getString(), 'provider' => TypeFactory::getString()],
                ),
                'transaction.get' => new Operation(
                    action: Backend\Action\Transaction\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/transaction/$transaction_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Backend\Transaction::class,
                ),
                'trash.getTypes' => new Operation(
                    action: Backend\Action\Trash\GetTypes::class,
                    httpMethod: 'GET',
                    httpPath: '/trash',
                    httpCode: 200,
                    outgoing: Model\Backend\TrashTypes::class,
                ),
                'trash.getAllByType' => new Operation(
                    action: Backend\Action\Trash\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/trash/:type',
                    httpCode: 200,
                    outgoing: Model\Backend\TrashDataCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'trash.restore' => new Operation(
                    action: Backend\Action\Trash\Restore::class,
                    httpMethod: 'POST',
                    httpPath: '/trash/:type',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\TrashRestore::class,
                ),
                'user.getAll' => new Operation(
                    action: Backend\Action\User\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/user',
                    httpCode: 200,
                    outgoing: Model\Backend\UserCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'user.create' => new Operation(
                    action: Backend\Action\User\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/user',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Backend\UserCreate::class,
                ),
                'user.get' => new Operation(
                    action: Backend\Action\User\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/user/$user_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Backend\User::class,
                ),
                'user.update' => new Operation(
                    action: Backend\Action\User\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/user/$user_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\UserUpdate::class,
                ),
                'user.delete' => new Operation(
                    action: Backend\Action\User\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/user/$user_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
            ],
            'consumer' => [
                'app.getAll' => new Operation(
                    action: Consumer\Action\App\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/app',
                    httpCode: 200,
                    outgoing: Model\Consumer\AppCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'app.create' => new Operation(
                    action: Consumer\Action\App\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/app',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Consumer\AppCreate::class,
                ),
                'app.get' => new Operation(
                    action: Consumer\Action\App\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/app/$app_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Consumer\App::class,
                ),
                'app.update' => new Operation(
                    action: Consumer\Action\App\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/app/$app_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Consumer\AppUpdate::class,
                ),
                'app.delete' => new Operation(
                    action: Consumer\Action\App\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/app/$app_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'event.getAll' => new Operation(
                    action: Consumer\Action\Event\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/event',
                    httpCode: 200,
                    outgoing: Model\Consumer\EventCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'grant.getAll' => new Operation(
                    action: Consumer\Action\Grant\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/grant',
                    httpCode: 200,
                    outgoing: Model\Consumer\GrantCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'grant.delete' => new Operation(
                    action: Consumer\Action\Grant\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/grant/$grant_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'log.getAll' => new Operation(
                    action: Consumer\Action\Log\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/log',
                    httpCode: 200,
                    outgoing: Model\Consumer\LogCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'log.get' => new Operation(
                    action: Consumer\Action\Log\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/log/$log_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Consumer\Log::class,
                ),
                'page.getAll' => new Operation(
                    action: Consumer\Action\Page\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/page',
                    httpCode: 200,
                    outgoing: Model\Consumer\PageCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'page.get' => new Operation(
                    action: Consumer\Action\Page\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/page/:page_id',
                    httpCode: 200,
                    outgoing: Model\Consumer\Page::class,
                ),
                'payment.portal' => new Operation(
                    action: Consumer\Action\Payment\Portal::class,
                    httpMethod: 'POST',
                    httpPath: '/payment/:provider/portal',
                    httpCode: 200,
                    outgoing: Model\Consumer\PaymentPortalResponse::class,
                    incoming: Model\Consumer\PaymentPortalRequest::class,
                ),
                'payment.checkout' => new Operation(
                    action: Consumer\Action\Payment\Checkout::class,
                    httpMethod: 'POST',
                    httpPath: '/payment/:provider/checkout',
                    httpCode: 200,
                    outgoing: Model\Consumer\PaymentCheckoutRequest::class,
                    incoming: Model\Consumer\PaymentCheckoutResponse::class,
                ),
                'plan.getAll' => new Operation(
                    action: Consumer\Action\Plan\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/plan',
                    httpCode: 200,
                    outgoing: Model\Consumer\PlanCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'plan.get' => new Operation(
                    action: Consumer\Action\Plan\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/plan/$plan_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Consumer\Plan::class,
                ),
                'scope.getAll' => new Operation(
                    action: Consumer\Action\Scope\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/scope',
                    httpCode: 200,
                    outgoing: Model\Consumer\ScopeCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'subscription.getAll' => new Operation(
                    action: Consumer\Action\Event\Subscription\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/subscription',
                    httpCode: 200,
                    outgoing: Model\Consumer\EventSubscriptionCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'subscription.create' => new Operation(
                    action: Consumer\Action\Event\Subscription\Create::class,
                    httpMethod: 'POST',
                    httpPath: '/subscription',
                    httpCode: 201,
                    outgoing: Message::class,
                    incoming: Model\Consumer\EventSubscriptionCreate::class,
                ),
                'subscription.get' => new Operation(
                    action: Consumer\Action\Event\Subscription\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/subscription/$subscription_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Consumer\EventSubscription::class,
                ),
                'subscription.update' => new Operation(
                    action: Consumer\Action\Event\Subscription\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/subscription/$subscription_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Consumer\EventSubscriptionUpdate::class,
                ),
                'subscription.delete' => new Operation(
                    action: Consumer\Action\Event\Subscription\Delete::class,
                    httpMethod: 'DELETE',
                    httpPath: '/subscription/$subscription_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'transaction.getAll' => new Operation(
                    action: Consumer\Action\Transaction\GetAll::class,
                    httpMethod: 'GET',
                    httpPath: '/transaction',
                    httpCode: 200,
                    outgoing: Model\Consumer\TransactionCollection::class,
                    parameters: ['startIndex' => TypeFactory::getInteger(), 'count' => TypeFactory::getInteger(), 'search' => TypeFactory::getString()],
                ),
                'transaction.get' => new Operation(
                    action: Consumer\Action\Transaction\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/transaction/$transaction_id<[0-9]+>',
                    httpCode: 200,
                    outgoing: Model\Consumer\Transaction::class,
                ),
                'account.get' => new Operation(
                    action: Consumer\Action\User\Get::class,
                    httpMethod: 'GET',
                    httpPath: '/account',
                    httpCode: 200,
                    outgoing: Model\Consumer\UserAccount::class,
                ),
                'account.update' => new Operation(
                    action: Consumer\Action\User\Update::class,
                    httpMethod: 'PUT',
                    httpPath: '/account',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Consumer\UserAccount::class,
                ),
                'account.changePassword' => new Operation(
                    action: Consumer\Action\User\ChangePassword::class,
                    httpMethod: 'PUT',
                    httpPath: '/account/change_password',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Backend\AccountChangePassword::class,
                ),
                'account.activate' => new Operation(
                    action: Consumer\Action\User\Activate::class,
                    httpMethod: 'POST',
                    httpPath: '/activate',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Consumer\UserActivate::class,
                    public: true,
                ),
                'account.authorize' => new Operation(
                    action: Consumer\Action\User\Authorize::class,
                    httpMethod: 'POST',
                    httpPath: '/authorize',
                    httpCode: 200,
                    outgoing: Model\Consumer\AuthorizeResponse::class,
                    incoming: Model\Consumer\AuthorizeRequest::class,
                ),
                'account.login' => new Operation(
                    action: Consumer\Action\User\Login::class,
                    httpMethod: 'POST',
                    httpPath: '/login',
                    httpCode: 200,
                    outgoing: Model\Consumer\UserJWT::class,
                    incoming: Model\Consumer\UserLogin::class,
                    public: true,
                ),
                'account.refresh' => new Operation(
                    action: Consumer\Action\User\Refresh::class,
                    httpMethod: 'PUT',
                    httpPath: '/login',
                    httpCode: 200,
                    outgoing: Model\Consumer\UserJWT::class,
                    incoming: Model\Consumer\UserRefresh::class,
                    public: true,
                ),
                'account.provider' => new Operation(
                    action: Consumer\Action\User\Provider::class,
                    httpMethod: 'POST',
                    httpPath: '/provider/:provider',
                    httpCode: 200,
                    outgoing: Model\Consumer\UserJWT::class,
                    incoming: Model\Consumer\UserProvider::class,
                    public: true,
                ),
                'account.register' => new Operation(
                    action: Consumer\Action\User\Register::class,
                    httpMethod: 'POST',
                    httpPath: '/register',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Consumer\UserRegister::class,
                    public: true,
                ),
                'account.requestPasswordReset' => new Operation(
                    action: Consumer\Action\User\ResetPassword\Request::class,
                    httpMethod: 'POST',
                    httpPath: '/password_reset',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Consumer\UserEmail::class,
                    public: true,
                ),
                'account.executePasswordReset' => new Operation(
                    action: Consumer\Action\User\ResetPassword\Execute::class,
                    httpMethod: 'PUT',
                    httpPath: '/password_reset',
                    httpCode: 200,
                    outgoing: Message::class,
                    incoming: Model\Consumer\UserPasswordReset::class,
                    public: true,
                ),
            ],
            'system' => [
                'getAbout' => new Operation(
                    action: System\Action\GetAbout::class,
                    httpMethod: 'GET',
                    httpPath: '/about',
                    httpCode: 200,
                    outgoing: Model\System\About::class,
                    public: true,
                ),
                'getAllRoutes' => new Operation(
                    action: System\Action\GetAllRoute::class,
                    httpMethod: 'GET',
                    httpPath: '/route',
                    httpCode: 200,
                    outgoing: Model\System\Route::class,
                    public: true,
                ),
                'getHealth' => new Operation(
                    action: System\Action\GetHealth::class,
                    httpMethod: 'GET',
                    httpPath: '/health',
                    httpCode: 200,
                    outgoing: Model\System\HealthCheck::class,
                    public: true,
                ),
                'getSchema' => new Operation(
                    action: System\Action\GetSchema::class,
                    httpMethod: 'GET',
                    httpPath: '/schema/:name',
                    httpCode: 200,
                    outgoing: Model\System\Schema::class,
                    public: true,
                ),
                'fetchConnectionCallback' => new Operation(
                    action: System\Action\ConnectionCallback::class,
                    httpMethod: 'GET',
                    httpPath: '/connection/:name/callback',
                    httpCode: 200,
                    outgoing: Message::class,
                    public: true,
                ),
            ],
            'authorization' => [
                'revoke' => new Operation(
                    action: Authorization\Action\Revoke::class,
                    httpMethod: 'POST',
                    httpPath: '/revoke',
                    httpCode: 200,
                    outgoing: Message::class,
                ),
                'getWhoami' => new Operation(
                    action: Authorization\Action\GetWhoami::class,
                    httpMethod: 'GET',
                    httpPath: '/whoami',
                    httpCode: 200,
                    outgoing: Model\Backend\User::class,
                ),
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

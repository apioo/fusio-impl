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
use Fusio\Impl\Connection\System;
use Fusio\Impl\Consumer;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Export;
use Fusio\Impl\Schema\Parser;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Framework\Controller\Generator;
use PSX\Framework\Controller\Tool;

/**
 * NewInstallation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class NewInstallation
{
    public static function getData()
    {
        $backendAppKey     = TokenGenerator::generateAppKey();
        $backendAppSecret  = TokenGenerator::generateAppSecret();
        $consumerAppKey    = TokenGenerator::generateAppKey();
        $consumerAppSecret = TokenGenerator::generateAppSecret();
        $password          = \password_hash(TokenGenerator::generateUserPassword(), PASSWORD_DEFAULT);

        $parser = new Parser();
        $now    = new \DateTime();
        $schema = self::getPassthruSchema();
        $cache  = $parser->parse($schema);

        $data = [
            'fusio_user' => [
                ['status' => 1, 'name' => 'Administrator', 'email' => 'admin@localhost.com', 'password' => $password, 'points' => null, 'date' => $now->format('Y-m-d H:i:s')],
            ],
            'fusio_action' => [
                ['status' => 1, 'name' => 'Welcome', 'class' => Welcome::class, 'engine' => PhpClass::class, 'config' => null, 'date' => $now->format('Y-m-d H:i:s')],
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
            'fusio_connection' => [
                ['status' => 1, 'name' => 'System', 'class' => System::class, 'config' => null],
            ],
            'fusio_cronjob' => [
            ],
            'fusio_event' => [
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.action.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.action.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.action.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.app.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.app.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.app.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.connection.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.connection.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.connection.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.cronjob.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.cronjob.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.cronjob.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.event.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.event.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.event.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.event.subscription.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.event.subscription.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.event.subscription.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.plan.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.plan.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.plan.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.rate.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.rate.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.rate.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.routes.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.routes.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.routes.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.schema.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.schema.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.schema.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.scope.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.scope.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.scope.update', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.user.create', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.user.delete', 'description' => ''],
                ['status' => Table\Event::STATUS_INTERNAL, 'name' => 'fusio.user.update', 'description' => ''],
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
                ['status' => 1, 'priority' => 0x10000000 | 67, 'methods' => 'ANY', 'path' => '/backend/account/change_password',             'controller' => Backend\Api\Account\ChangePassword::class],
                ['status' => 1, 'priority' => 0x10000000 | 66, 'methods' => 'ANY', 'path' => '/backend/action',                              'controller' => Backend\Api\Action\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 65, 'methods' => 'ANY', 'path' => '/backend/action/list',                         'controller' => Backend\Api\Action\Index::class],
                ['status' => 1, 'priority' => 0x10000000 | 64, 'methods' => 'ANY', 'path' => '/backend/action/form',                         'controller' => Backend\Api\Action\Form::class],
                ['status' => 1, 'priority' => 0x10000000 | 63, 'methods' => 'ANY', 'path' => '/backend/action/execute/$action_id<[0-9]+>',   'controller' => Backend\Api\Action\Execute::class],
                ['status' => 1, 'priority' => 0x10000000 | 62, 'methods' => 'ANY', 'path' => '/backend/action/$action_id<[0-9]+>',           'controller' => Backend\Api\Action\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 61, 'methods' => 'ANY', 'path' => '/backend/app/token',                           'controller' => Backend\Api\App\Token\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 60, 'methods' => 'ANY', 'path' => '/backend/app/token/$token_id<[0-9]+>',         'controller' => Backend\Api\App\Token\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 59, 'methods' => 'ANY', 'path' => '/backend/app',                                 'controller' => Backend\Api\App\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 58, 'methods' => 'ANY', 'path' => '/backend/app/$app_id<[0-9]+>',                 'controller' => Backend\Api\App\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 57, 'methods' => 'ANY', 'path' => '/backend/app/$app_id<[0-9]+>/token/:token_id', 'controller' => Backend\Api\App\Token::class],
                ['status' => 1, 'priority' => 0x10000000 | 56, 'methods' => 'ANY', 'path' => '/backend/audit',                               'controller' => Backend\Api\Audit\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 55, 'methods' => 'ANY', 'path' => '/backend/audit/$audit_id<[0-9]+>',             'controller' => Backend\Api\Audit\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 54, 'methods' => 'ANY', 'path' => '/backend/config',                              'controller' => Backend\Api\Config\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 53, 'methods' => 'ANY', 'path' => '/backend/config/$config_id<[0-9]+>',           'controller' => Backend\Api\Config\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 52, 'methods' => 'ANY', 'path' => '/backend/connection',                          'controller' => Backend\Api\Connection\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 51, 'methods' => 'ANY', 'path' => '/backend/connection/list',                     'controller' => Backend\Api\Connection\Index::class],
                ['status' => 1, 'priority' => 0x10000000 | 50, 'methods' => 'ANY', 'path' => '/backend/connection/form',                     'controller' => Backend\Api\Connection\Form::class],
                ['status' => 1, 'priority' => 0x10000000 | 49, 'methods' => 'ANY', 'path' => '/backend/connection/$connection_id<[0-9]+>',   'controller' => Backend\Api\Connection\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 48, 'methods' => 'ANY', 'path' => '/backend/cronjob',                             'controller' => Backend\Api\Cronjob\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 47, 'methods' => 'ANY', 'path' => '/backend/cronjob/$cronjob_id<[0-9]+>',         'controller' => Backend\Api\Cronjob\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 46, 'methods' => 'ANY', 'path' => '/backend/dashboard',                           'controller' => Backend\Api\Dashboard\Dashboard::class],
                ['status' => 1, 'priority' => 0x10000000 | 45, 'methods' => 'ANY', 'path' => '/backend/event/subscription',                  'controller' => Backend\Api\Event\Subscription\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 44, 'methods' => 'ANY', 'path' => '/backend/event/subscription/$subscription_id<[0-9]+>', 'controller' => Backend\Api\Event\Subscription\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 43, 'methods' => 'ANY', 'path' => '/backend/event',                               'controller' => Backend\Api\Event\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 42, 'methods' => 'ANY', 'path' => '/backend/event/$event_id<[0-9]+>',             'controller' => Backend\Api\Event\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 41, 'methods' => 'ANY', 'path' => '/backend/import/process',                      'controller' => Backend\Api\Import\Process::class],
                ['status' => 1, 'priority' => 0x10000000 | 40, 'methods' => 'ANY', 'path' => '/backend/import/:format',                      'controller' => Backend\Api\Import\Format::class],
                ['status' => 1, 'priority' => 0x10000000 | 39, 'methods' => 'ANY', 'path' => '/backend/log/error',                           'controller' => Backend\Api\Log\Error\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 38, 'methods' => 'ANY', 'path' => '/backend/log/error/$error_id<[0-9]+>',         'controller' => Backend\Api\Log\Error\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 37, 'methods' => 'ANY', 'path' => '/backend/log',                                 'controller' => Backend\Api\Log\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 36, 'methods' => 'ANY', 'path' => '/backend/log/$log_id<[0-9]+>',                 'controller' => Backend\Api\Log\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 35, 'methods' => 'ANY', 'path' => '/backend/marketplace',                         'controller' => Backend\Api\Marketplace\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 34, 'methods' => 'ANY', 'path' => '/backend/marketplace/:app_name',               'controller' => Backend\Api\Marketplace\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 33, 'methods' => 'ANY', 'path' => '/backend/plan/contract',                       'controller' => Backend\Api\Plan\Contract\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 32, 'methods' => 'ANY', 'path' => '/backend/plan/contract/$contract_id<[0-9]+>',  'controller' => Backend\Api\Plan\Contract\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 31, 'methods' => 'ANY', 'path' => '/backend/plan/invoice',                        'controller' => Backend\Api\Plan\Invoice\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 30, 'methods' => 'ANY', 'path' => '/backend/plan/invoice/$invoice_id<[0-9]+>',    'controller' => Backend\Api\Plan\Invoice\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 29, 'methods' => 'ANY', 'path' => '/backend/plan',                                'controller' => Backend\Api\Plan\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 28, 'methods' => 'ANY', 'path' => '/backend/plan/$plan_id<[0-9]+>',               'controller' => Backend\Api\Plan\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 27, 'methods' => 'ANY', 'path' => '/backend/rate',                                'controller' => Backend\Api\Rate\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 26, 'methods' => 'ANY', 'path' => '/backend/rate/$rate_id<[0-9]+>',               'controller' => Backend\Api\Rate\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 25, 'methods' => 'ANY', 'path' => '/backend/routes',                              'controller' => Backend\Api\Route\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 24, 'methods' => 'ANY', 'path' => '/backend/routes/provider',                     'controller' => Backend\Api\Route\Index::class],
                ['status' => 1, 'priority' => 0x10000000 | 23, 'methods' => 'ANY', 'path' => '/backend/routes/provider/:provider',           'controller' => Backend\Api\Route\Provider::class],
                ['status' => 1, 'priority' => 0x10000000 | 22, 'methods' => 'ANY', 'path' => '/backend/routes/$route_id<[0-9]+>',            'controller' => Backend\Api\Route\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 21, 'methods' => 'ANY', 'path' => '/backend/schema',                              'controller' => Backend\Api\Schema\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 20, 'methods' => 'ANY', 'path' => '/backend/schema/preview/$schema_id<[0-9]+>',   'controller' => Backend\Api\Schema\Preview::class],
                ['status' => 1, 'priority' => 0x10000000 | 19, 'methods' => 'ANY', 'path' => '/backend/schema/form/$schema_id<[0-9]+>',      'controller' => Backend\Api\Schema\Form::class],
                ['status' => 1, 'priority' => 0x10000000 | 18, 'methods' => 'ANY', 'path' => '/backend/schema/$schema_id<[0-9]+>',           'controller' => Backend\Api\Schema\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 17, 'methods' => 'ANY', 'path' => '/backend/scope',                               'controller' => Backend\Api\Scope\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 16, 'methods' => 'ANY', 'path' => '/backend/scope/$scope_id<[0-9]+>',             'controller' => Backend\Api\Scope\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 15, 'methods' => 'ANY', 'path' => '/backend/sdk',                                 'controller' => Backend\Api\Sdk\Generate::class],
                ['status' => 1, 'priority' => 0x10000000 | 14, 'methods' => 'ANY', 'path' => '/backend/statistic/count_requests',            'controller' => Backend\Api\Statistic\CountRequests::class],
                ['status' => 1, 'priority' => 0x10000000 | 13, 'methods' => 'ANY', 'path' => '/backend/statistic/errors_per_route',          'controller' => Backend\Api\Statistic\ErrorsPerRoute::class],
                ['status' => 1, 'priority' => 0x10000000 | 12, 'methods' => 'ANY', 'path' => '/backend/statistic/incoming_requests',         'controller' => Backend\Api\Statistic\IncomingRequests::class],
                ['status' => 1, 'priority' => 0x10000000 | 11, 'methods' => 'ANY', 'path' => '/backend/statistic/incoming_transactions',     'controller' => Backend\Api\Statistic\IncomingTransactions::class],
                ['status' => 1, 'priority' => 0x10000000 | 10, 'methods' => 'ANY', 'path' => '/backend/statistic/issued_tokens',             'controller' => Backend\Api\Statistic\IssuedTokens::class],
                ['status' => 1, 'priority' => 0x10000000 | 9,  'methods' => 'ANY', 'path' => '/backend/statistic/most_used_apps',            'controller' => Backend\Api\Statistic\MostUsedApps::class],
                ['status' => 1, 'priority' => 0x10000000 | 8,  'methods' => 'ANY', 'path' => '/backend/statistic/most_used_routes',          'controller' => Backend\Api\Statistic\MostUsedRoutes::class],
                ['status' => 1, 'priority' => 0x10000000 | 7,  'methods' => 'ANY', 'path' => '/backend/statistic/time_average',              'controller' => Backend\Api\Statistic\TimeAverage::class],
                ['status' => 1, 'priority' => 0x10000000 | 6,  'methods' => 'ANY', 'path' => '/backend/statistic/time_per_route',            'controller' => Backend\Api\Statistic\TimePerRoute::class],
                ['status' => 1, 'priority' => 0x10000000 | 5,  'methods' => 'ANY', 'path' => '/backend/statistic/used_points',               'controller' => Backend\Api\Statistic\UsedPoints::class],
                ['status' => 1, 'priority' => 0x10000000 | 4,  'methods' => 'ANY', 'path' => '/backend/transaction',                         'controller' => Backend\Api\Transaction\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 3,  'methods' => 'ANY', 'path' => '/backend/transaction/$transaction_id<[0-9]+>', 'controller' => Backend\Api\Transaction\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 2,  'methods' => 'ANY', 'path' => '/backend/user',                                'controller' => Backend\Api\User\Collection::class],
                ['status' => 1, 'priority' => 0x10000000 | 1,  'methods' => 'ANY', 'path' => '/backend/user/$user_id<[0-9]+>',               'controller' => Backend\Api\User\Entity::class],
                ['status' => 1, 'priority' => 0x10000000 | 0,  'methods' => 'ANY', 'path' => '/backend/token',                               'controller' => Backend\Authorization\Token::class],

                ['status' => 1, 'priority' => 0x8000000 | 26, 'methods' => 'ANY', 'path' => '/consumer/app',                                 'controller' => Consumer\Api\App\Collection::class],
                ['status' => 1, 'priority' => 0x8000000 | 25, 'methods' => 'ANY', 'path' => '/consumer/app/$app_id<[0-9]+>',                 'controller' => Consumer\Api\App\Entity::class],
                ['status' => 1, 'priority' => 0x8000000 | 24, 'methods' => 'ANY', 'path' => '/consumer/event',                               'controller' => Consumer\Api\Event\Collection::class],
                ['status' => 1, 'priority' => 0x8000000 | 23, 'methods' => 'ANY', 'path' => '/consumer/grant',                               'controller' => Consumer\Api\Grant\Collection::class],
                ['status' => 1, 'priority' => 0x8000000 | 22, 'methods' => 'ANY', 'path' => '/consumer/grant/$grant_id<[0-9]+>',             'controller' => Consumer\Api\Grant\Entity::class],
                ['status' => 1, 'priority' => 0x8000000 | 21, 'methods' => 'ANY', 'path' => '/consumer/plan/contract',                       'controller' => Consumer\Api\Plan\Contract\Collection::class],
                ['status' => 1, 'priority' => 0x8000000 | 20, 'methods' => 'ANY', 'path' => '/consumer/plan/contract/$contract_id<[0-9]+>',  'controller' => Consumer\Api\Plan\Contract\Entity::class],
                ['status' => 1, 'priority' => 0x8000000 | 19, 'methods' => 'ANY', 'path' => '/consumer/plan/invoice',                        'controller' => Consumer\Api\Plan\Invoice\Collection::class],
                ['status' => 1, 'priority' => 0x8000000 | 18, 'methods' => 'ANY', 'path' => '/consumer/plan/invoice/$invoice_id<[0-9]+>',    'controller' => Consumer\Api\Plan\Invoice\Entity::class],
                ['status' => 1, 'priority' => 0x8000000 | 17, 'methods' => 'ANY', 'path' => '/consumer/plan',                                'controller' => Consumer\Api\Plan\Collection::class],
                ['status' => 1, 'priority' => 0x8000000 | 16, 'methods' => 'ANY', 'path' => '/consumer/plan/$plan_id<[0-9]+>',               'controller' => Consumer\Api\Plan\Entity::class],
                ['status' => 1, 'priority' => 0x8000000 | 15, 'methods' => 'ANY', 'path' => '/consumer/scope',                               'controller' => Consumer\Api\Scope\Collection::class],
                ['status' => 1, 'priority' => 0x8000000 | 14, 'methods' => 'ANY', 'path' => '/consumer/subscription',                        'controller' => Consumer\Api\Event\Subscription\Collection::class],
                ['status' => 1, 'priority' => 0x8000000 | 13, 'methods' => 'ANY', 'path' => '/consumer/subscription/$subscription_id<[0-9]+>', 'controller' => Consumer\Api\Event\Subscription\Entity::class],
                ['status' => 1, 'priority' => 0x8000000 | 12, 'methods' => 'ANY', 'path' => '/consumer/transaction',                         'controller' => Consumer\Api\Transaction\Collection::class],
                ['status' => 1, 'priority' => 0x8000000 | 11, 'methods' => 'ANY', 'path' => '/consumer/transaction/execute/:transaction_id', 'controller' => Consumer\Api\Transaction\Execute::class],
                ['status' => 1, 'priority' => 0x8000000 | 10,  'methods' => 'ANY', 'path' => '/consumer/transaction/prepare/:provider',       'controller' => Consumer\Api\Transaction\Prepare::class],
                ['status' => 1, 'priority' => 0x8000000 | 9,  'methods' => 'ANY', 'path' => '/consumer/transaction/$transaction_id<[0-9]+>', 'controller' => Consumer\Api\Transaction\Entity::class],
                ['status' => 1, 'priority' => 0x8000000 | 8,  'methods' => 'ANY', 'path' => '/consumer/account',                             'controller' => Consumer\Api\User\Account::class],
                ['status' => 1, 'priority' => 0x8000000 | 7,  'methods' => 'ANY', 'path' => '/consumer/activate',                            'controller' => Consumer\Api\User\Activate::class],
                ['status' => 1, 'priority' => 0x8000000 | 6,  'methods' => 'ANY', 'path' => '/consumer/authorize',                           'controller' => Consumer\Api\User\Authorize::class],
                ['status' => 1, 'priority' => 0x8000000 | 5,  'methods' => 'ANY', 'path' => '/consumer/account/change_password',             'controller' => Consumer\Api\User\ChangePassword::class],
                ['status' => 1, 'priority' => 0x8000000 | 4,  'methods' => 'ANY', 'path' => '/consumer/login',                               'controller' => Consumer\Api\User\Login::class],
                ['status' => 1, 'priority' => 0x8000000 | 3,  'methods' => 'ANY', 'path' => '/consumer/provider/:provider',                  'controller' => Consumer\Api\User\Provider::class],
                ['status' => 1, 'priority' => 0x8000000 | 2,  'methods' => 'ANY', 'path' => '/consumer/register',                            'controller' => Consumer\Api\User\Register::class],
                ['status' => 1, 'priority' => 0x8000000 | 1,  'methods' => 'ANY', 'path' => '/consumer/password_reset',                      'controller' => Consumer\Api\User\PasswordReset::class],
                ['status' => 1, 'priority' => 0x8000000 | 0,  'methods' => 'ANY', 'path' => '/consumer/token',                               'controller' => Consumer\Authorization\Token::class],

                ['status' => 1, 'priority' => 0x4000000 | 2,   'methods' => 'ANY', 'path' => '/authorization/revoke',                        'controller' => Authorization\Revoke::class],
                ['status' => 1, 'priority' => 0x4000000 | 1,   'methods' => 'ANY', 'path' => '/authorization/token',                         'controller' => Authorization\Token::class],
                ['status' => 1, 'priority' => 0x4000000 | 0,   'methods' => 'ANY', 'path' => '/authorization/whoami',                        'controller' => Authorization\Whoami::class],

                ['status' => 1, 'priority' => 0x2000000 | 1,   'methods' => 'GET', 'path' => '/doc',                                         'controller' => Tool\Documentation\IndexController::class],
                ['status' => 1, 'priority' => 0x2000000 | 0,   'methods' => 'GET', 'path' => '/doc/:version/*path',                          'controller' => Tool\Documentation\DetailController::class],

                ['status' => 1, 'priority' => 0x1000000 | 5,   'methods' => 'ANY', 'path' => '/export/routes',                               'controller' => Export\Api\Routes::class],
                ['status' => 1, 'priority' => 0x1000000 | 4,   'methods' => 'ANY', 'path' => '/export/jsonrpc',                              'controller' => Export\Api\JsonRpc::class],
                ['status' => 1, 'priority' => 0x1000000 | 3,   'methods' => 'ANY', 'path' => '/export/health',                               'controller' => Export\Api\Health::class],
                ['status' => 1, 'priority' => 0x1000000 | 2,   'methods' => 'ANY', 'path' => '/export/debug',                                'controller' => Export\Api\Debug::class],
                ['status' => 1, 'priority' => 0x1000000 | 1,   'methods' => 'ANY', 'path' => '/export/schema/:name',                         'controller' => Export\Api\Schema::class],
                ['status' => 1, 'priority' => 0x1000000 | 0,   'methods' => 'GET', 'path' => '/export/:type/:version/*path',                 'controller' => Generator\GeneratorController::class],

                ['status' => 1, 'priority' => 0,               'methods' => 'ANY', 'path' => '/',                                            'controller' => SchemaApiController::class],
            ],
            'fusio_schema' => [
                ['status' => 1, 'name' => 'Passthru', 'source' => $schema, 'cache' => $cache, 'form' => null]
            ],
            'fusio_scope' => [
                ['name' => 'backend', 'description' => 'Global access to the backend API'],
                ['name' => 'consumer', 'description' => 'Global access to the consumer API'],
                ['name' => 'authorization', 'description' => 'Authorization API endpoint'],
                ['name' => 'backend.account', 'description' => 'Option to change the password of your account'],
                ['name' => 'backend.action', 'description' => 'View and manage actions'],
                ['name' => 'backend.app', 'description' => 'View and manage apps'],
                ['name' => 'backend.audit', 'description' => 'View audits'],
                ['name' => 'backend.config', 'description' => 'View and edit config entries'],
                ['name' => 'backend.connection', 'description' => 'View and manage connections'],
                ['name' => 'backend.cronjob', 'description' => 'View and manage cronjob entries'],
                ['name' => 'backend.dashboard', 'description' => 'View dashboard statistic'],
                ['name' => 'backend.event', 'description' => 'View and manage events'],
                ['name' => 'backend.import', 'description' => 'Execute import'],
                ['name' => 'backend.log', 'description' => 'View logs'],
                ['name' => 'backend.marketplace', 'description' => 'View and manage apps from the marketplace'],
                ['name' => 'backend.plan', 'description' => 'View and manage plans'],
                ['name' => 'backend.rate', 'description' => 'View and manage rates'],
                ['name' => 'backend.routes', 'description' => 'View and manage routes'],
                ['name' => 'backend.schema', 'description' => 'View and manage schemas'],
                ['name' => 'backend.scope', 'description' => 'View and manage scopes'],
                ['name' => 'backend.sdk', 'description' => 'Generate client SDKs'],
                ['name' => 'backend.statistic', 'description' => 'View statistics'],
                ['name' => 'backend.transaction', 'description' => 'View transactions'],
                ['name' => 'backend.user', 'description' => 'View and manage users'],
                ['name' => 'consumer.app', 'description' => 'View and manage your apps'],
                ['name' => 'consumer.event', 'description' => 'View and manage your events'],
                ['name' => 'consumer.grant', 'description' => 'View and manage your grants'],
                ['name' => 'consumer.plan', 'description' => 'View available plans'],
                ['name' => 'consumer.scope', 'description' => 'View available scopes'],
                ['name' => 'consumer.subscription', 'description' => 'View and manage your subscriptions'],
                ['name' => 'consumer.transaction', 'description' => 'Execute transactions'],
                ['name' => 'consumer.user', 'description' => 'Edit your account settings'],
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

        // routes method
        $lastRouteId = count($data['fusio_routes']);
        $data['fusio_routes_method'][] = ['route_id' => $lastRouteId, 'method' => 'GET', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'parameters' => null, 'request' => null, 'action' => 1, 'costs' => null];
        $data['fusio_routes_response'][] = ['method_id' => 1, 'code' => 200, 'response' => 1];

        // scope routes
        foreach ($data['fusio_routes'] as $index => $row) {
            $scopeId = self::getScopeIdFromPath($row['path'], $data['fusio_scope']);
            if ($scopeId !== null) {
                $data['fusio_scope_routes'][] = ['scope_id' => $scopeId, 'route_id' => $index + 1, 'allow' => 1, 'methods' => 'GET|POST|PUT|PATCH|DELETE'];
            }
        }

        return $data;
    }

    private static function getPassthruSchema()
    {
        return json_encode([
            'id' => 'http://fusio-project.org',
            'title' => 'passthru',
            'type' => 'object',
            'description' => 'No schema was specified.',
            'additionalProperties' => true,
        ], JSON_PRETTY_PRINT);
    }

    public static function getScopeIdFromPath($path, array $scopes = null)
    {
        if (!empty($scopes)) {
            $parts = array_values(array_filter(explode('/', $path)));

            $id = null;
            if (count($parts) > 1) {
                $id = self::findScope($scopes, $parts[0] . '.' . $parts[1]);
            } elseif (count($parts) > 0) {
                $id = self::findScope($scopes, $parts[0]);
            }

            if ($id !== null) {
                return $id;
            }
        }

        if (strpos($path, '/backend') === 0) {
            return 1;
        } elseif (strpos($path, '/consumer') === 0) {
            return 2;
        } elseif (strpos($path, '/authorization') === 0) {
            return 3;
        }

        return null;
    }

    private static function findScope(array $scopes, string $name)
    {
        foreach ($scopes as $index => $scope) {
            if ($scope['name'] == $name) {
                return $index + 1;
            }
        }

        return null;
    }
}

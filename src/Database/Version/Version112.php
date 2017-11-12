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

namespace Fusio\Impl\Database\Version;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Fusio\Adapter;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Impl\Action\Welcome;
use Fusio\Impl\Authorization;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Backend;
use Fusio\Impl\Consumer;
use Fusio\Impl\Controller\SchemaApiController;
use Fusio\Impl\Database\VersionInterface;
use Fusio\Impl\Schema\Parser;
use Fusio\Impl\Service\User\ProviderInterface;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Framework\Controller\Generator;
use PSX\Framework\Controller\Tool;

/**
 * Version112
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Version112 implements VersionInterface
{
    public function getSchema()
    {
        $schema = new Schema();

        $actionTable = $schema->createTable('fusio_action');
        $actionTable->addColumn('id', 'integer', array('autoincrement' => true));
        $actionTable->addColumn('status', 'integer', array('default' => Table\Action::STATUS_ACTIVE));
        $actionTable->addColumn('name', 'string', array('length' => 64));
        $actionTable->addColumn('class', 'string', array('length' => 255));
        $actionTable->addColumn('engine', 'string', array('length' => 255, 'notnull' => false));
        $actionTable->addColumn('config', 'blob', array('notnull' => false));
        $actionTable->addColumn('date', 'datetime');
        $actionTable->setPrimaryKey(array('id'));

        $actionClassTable = $schema->createTable('fusio_action_class');
        $actionClassTable->addColumn('id', 'integer', array('autoincrement' => true));
        $actionClassTable->addColumn('class', 'string', array('length' => 255));
        $actionClassTable->setPrimaryKey(array('id'));
        $actionClassTable->addUniqueIndex(array('class'));

        $appTable = $schema->createTable('fusio_app');
        $appTable->addColumn('id', 'integer', array('autoincrement' => true));
        $appTable->addColumn('userId', 'integer');
        $appTable->addColumn('status', 'integer');
        $appTable->addColumn('name', 'string', array('length' => 64));
        $appTable->addColumn('url', 'string', array('length' => 255));
        $appTable->addColumn('parameters', 'string', array('length' => 255, 'notnull' => false));
        $appTable->addColumn('appKey', 'string', array('length' => 255));
        $appTable->addColumn('appSecret', 'string', array('length' => 255));
        $appTable->addColumn('date', 'datetime');
        $appTable->setPrimaryKey(array('id'));
        $appTable->addUniqueIndex(array('appKey'));

        $appScopeTable = $schema->createTable('fusio_app_scope');
        $appScopeTable->addColumn('id', 'integer', array('autoincrement' => true));
        $appScopeTable->addColumn('appId', 'integer');
        $appScopeTable->addColumn('scopeId', 'integer');
        $appScopeTable->setPrimaryKey(array('id'));
        $appScopeTable->addUniqueIndex(array('appId', 'scopeId'));

        $appTokenTable = $schema->createTable('fusio_app_token');
        $appTokenTable->addColumn('id', 'integer', array('autoincrement' => true));
        $appTokenTable->addColumn('appId', 'integer');
        $appTokenTable->addColumn('userId', 'integer');
        $appTokenTable->addColumn('status', 'integer', array('default' => 1));
        $appTokenTable->addColumn('token', 'string', array('length' => 255));
        $appTokenTable->addColumn('refresh', 'string', array('length' => 255, 'notnull' => false));
        $appTokenTable->addColumn('scope', 'string', array('length' => 255));
        $appTokenTable->addColumn('ip', 'string', array('length' => 40));
        $appTokenTable->addColumn('expire', 'datetime', array('notnull' => false));
        $appTokenTable->addColumn('date', 'datetime');
        $appTokenTable->setPrimaryKey(array('id'));
        $appTokenTable->addUniqueIndex(array('token'));

        $appCodeTable = $schema->createTable('fusio_app_code');
        $appCodeTable->addColumn('id', 'integer', array('autoincrement' => true));
        $appCodeTable->addColumn('appId', 'integer');
        $appCodeTable->addColumn('userId', 'integer');
        $appCodeTable->addColumn('code', 'string', array('length' => 255));
        $appCodeTable->addColumn('redirectUri', 'string', array('length' => 255, 'notnull' => false));
        $appCodeTable->addColumn('scope', 'string', array('length' => 255));
        $appCodeTable->addColumn('date', 'datetime');
        $appCodeTable->setPrimaryKey(array('id'));
        $appCodeTable->addUniqueIndex(array('code'));

        $auditTable = $schema->createTable('fusio_audit');
        $auditTable->addColumn('id', 'integer', array('autoincrement' => true));
        $auditTable->addColumn('appId', 'integer');
        $auditTable->addColumn('userId', 'integer');
        $auditTable->addColumn('refId', 'integer', array('notnull' => false));
        $auditTable->addColumn('event', 'string');
        $auditTable->addColumn('ip', 'string', array('length' => 40));
        $auditTable->addColumn('message', 'string');
        $auditTable->addColumn('content', 'text', array('notnull' => false));
        $auditTable->addColumn('date', 'datetime');
        $auditTable->setPrimaryKey(array('id'));
        $auditTable->addOption('engine', 'MyISAM');

        $configTable = $schema->createTable('fusio_config');
        $configTable->addColumn('id', 'integer', array('autoincrement' => true));
        $configTable->addColumn('type', 'integer', array('default' => 1));
        $configTable->addColumn('name', 'string', array('length' => 64));
        $configTable->addColumn('description', 'string', array('length' => 255));
        $configTable->addColumn('value', 'string');
        $configTable->setPrimaryKey(array('id'));
        $configTable->addUniqueIndex(array('name'));

        $connectionTable = $schema->createTable('fusio_connection');
        $connectionTable->addColumn('id', 'integer', array('autoincrement' => true));
        $connectionTable->addColumn('status', 'integer', array('default' => Table\Connection::STATUS_ACTIVE));
        $connectionTable->addColumn('name', 'string', array('length' => 64));
        $connectionTable->addColumn('class', 'string', array('length' => 255));
        $connectionTable->addColumn('config', 'blob', array('notnull' => false));
        $connectionTable->setPrimaryKey(array('id'));
        $connectionTable->addUniqueIndex(array('name'));

        $connectionClassTable = $schema->createTable('fusio_connection_class');
        $connectionClassTable->addColumn('id', 'integer', array('autoincrement' => true));
        $connectionClassTable->addColumn('class', 'string', array('length' => 255));
        $connectionClassTable->setPrimaryKey(array('id'));
        $connectionClassTable->addUniqueIndex(array('class'));

        $cronjobTable = $schema->createTable('fusio_cronjob');
        $cronjobTable->addColumn('id', 'integer', array('autoincrement' => true));
        $cronjobTable->addColumn('status', 'integer', array('default' => Table\Cronjob::STATUS_ACTIVE));
        $cronjobTable->addColumn('name', 'string', array('length' => 64));
        $cronjobTable->addColumn('cron', 'string');
        $cronjobTable->addColumn('action', 'integer', array('notnull' => false));
        $cronjobTable->addColumn('executeDate', 'datetime', array('notnull' => false));
        $cronjobTable->addColumn('exitCode', 'integer', array('notnull' => false));
        $cronjobTable->setPrimaryKey(array('id'));
        $cronjobTable->addUniqueIndex(array('name'));

        $cronjobErrorTable = $schema->createTable('fusio_cronjob_error');
        $cronjobErrorTable->addColumn('id', 'integer', array('autoincrement' => true));
        $cronjobErrorTable->addColumn('cronjobId', 'integer');
        $cronjobErrorTable->addColumn('message', 'string', array('length' => 500));
        $cronjobErrorTable->addColumn('trace', 'text');
        $cronjobErrorTable->addColumn('file', 'string', array('length' => 255));
        $cronjobErrorTable->addColumn('line', 'integer');
        $cronjobErrorTable->setPrimaryKey(array('id'));
        $cronjobErrorTable->addOption('engine', 'MyISAM');

        $deployMigrationTable = $schema->createTable('fusio_deploy_migration');
        $deployMigrationTable->addColumn('id', 'integer', array('autoincrement' => true));
        $deployMigrationTable->addColumn('connection', 'string', array('length' => 32));
        $deployMigrationTable->addColumn('file', 'string', array('length' => 128));
        $deployMigrationTable->addColumn('fileHash', 'string', array('length' => 40));
        $deployMigrationTable->addColumn('executeDate', 'datetime');
        $deployMigrationTable->setPrimaryKey(array('id'));

        $logTable = $schema->createTable('fusio_log');
        $logTable->addColumn('id', 'integer', array('autoincrement' => true));
        $logTable->addColumn('routeId', 'integer', array('notnull' => false));
        $logTable->addColumn('appId', 'integer', array('notnull' => false));
        $logTable->addColumn('userId', 'integer', array('notnull' => false));
        $logTable->addColumn('ip', 'string', array('length' => 40));
        $logTable->addColumn('userAgent', 'string', array('length' => 255));
        $logTable->addColumn('method', 'string', array('length' => 16));
        $logTable->addColumn('path', 'string', array('length' => 1023));
        $logTable->addColumn('header', 'text');
        $logTable->addColumn('body', 'text', array('notnull' => false));
        $logTable->addColumn('executionTime', 'integer', array('notnull' => false, 'default' => null));
        $logTable->addColumn('date', 'datetime');
        $logTable->setPrimaryKey(array('id'));
        $logTable->addOption('engine', 'MyISAM');

        $logErrorTable = $schema->createTable('fusio_log_error');
        $logErrorTable->addColumn('id', 'integer', array('autoincrement' => true));
        $logErrorTable->addColumn('logId', 'integer');
        $logErrorTable->addColumn('message', 'string', array('length' => 500));
        $logErrorTable->addColumn('trace', 'text');
        $logErrorTable->addColumn('file', 'string', array('length' => 255));
        $logErrorTable->addColumn('line', 'integer');
        $logErrorTable->setPrimaryKey(array('id'));
        $logErrorTable->addOption('engine', 'MyISAM');

        $routesTable = $schema->createTable('fusio_routes');
        $routesTable->addColumn('id', 'integer', array('autoincrement' => true));
        $routesTable->addColumn('status', 'integer', array('default' => Table\Routes::STATUS_ACTIVE));
        $routesTable->addColumn('methods', 'string', array('length' => 64));
        $routesTable->addColumn('path', 'string', array('length' => 255));
        $routesTable->addColumn('controller', 'string', array('length' => 255));
        $routesTable->addColumn('config', 'blob', array('notnull' => false));
        $routesTable->setPrimaryKey(array('id'));

        $routesMethodTable = $schema->createTable('fusio_routes_method');
        $routesMethodTable->addColumn('id', 'integer', array('autoincrement' => true));
        $routesMethodTable->addColumn('routeId', 'integer');
        $routesMethodTable->addColumn('method', 'string', array('length' => 8));
        $routesMethodTable->addColumn('version', 'integer');
        $routesMethodTable->addColumn('status', 'integer');
        $routesMethodTable->addColumn('active', 'integer', array('default' => 0));
        $routesMethodTable->addColumn('public', 'integer', array('default' => 0));
        $routesMethodTable->addColumn('parameters', 'integer', array('notnull' => false));
        $routesMethodTable->addColumn('request', 'integer', array('notnull' => false));
        $routesMethodTable->addColumn('action', 'integer', array('notnull' => false));
        $routesMethodTable->addColumn('schemaCache', 'text', array('notnull' => false));
        $routesMethodTable->addColumn('actionCache', 'text', array('notnull' => false));
        $routesMethodTable->setPrimaryKey(array('id'));
        $routesMethodTable->addUniqueIndex(array('routeId', 'method', 'version'));

        $routesResponseTable = $schema->createTable('fusio_routes_response');
        $routesResponseTable->addColumn('id', 'integer', array('autoincrement' => true));
        $routesResponseTable->addColumn('methodId', 'integer');
        $routesResponseTable->addColumn('code', 'smallint');
        $routesResponseTable->addColumn('response', 'integer');
        $routesResponseTable->setPrimaryKey(array('id'));

        $schemaTable = $schema->createTable('fusio_schema');
        $schemaTable->addColumn('id', 'integer', array('autoincrement' => true));
        $schemaTable->addColumn('status', 'integer', array('default' => Table\Schema::STATUS_ACTIVE));
        $schemaTable->addColumn('name', 'string', array('length' => 64));
        $schemaTable->addColumn('source', 'text');
        $schemaTable->addColumn('cache', 'blob');
        $schemaTable->setPrimaryKey(array('id'));
        $schemaTable->addUniqueIndex(array('name'));

        $scopeTable = $schema->createTable('fusio_scope');
        $scopeTable->addColumn('id', 'integer', array('autoincrement' => true));
        $scopeTable->addColumn('name', 'string', array('length' => 32));
        $scopeTable->addColumn('description', 'string', array('length' => 255));
        $scopeTable->setPrimaryKey(array('id'));
        $scopeTable->addUniqueIndex(array('name'));

        $metaTable = $schema->createTable('fusio_meta');
        $metaTable->addColumn('id', 'integer', array('autoincrement' => true));
        $metaTable->addColumn('version', 'string', array('length' => 16));
        $metaTable->addColumn('installDate', 'datetime');
        $metaTable->setPrimaryKey(array('id'));

        $rateTable = $schema->createTable('fusio_rate');
        $rateTable->addColumn('id', 'integer', array('autoincrement' => true));
        $rateTable->addColumn('status', 'integer');
        $rateTable->addColumn('priority', 'integer');
        $rateTable->addColumn('name', 'string', array('length' => 64));
        $rateTable->addColumn('rateLimit', 'integer');
        $rateTable->addColumn('timespan', 'string');
        $rateTable->setPrimaryKey(array('id'));

        $rateAllocationTable = $schema->createTable('fusio_rate_allocation');
        $rateAllocationTable->addColumn('id', 'integer', array('autoincrement' => true));
        $rateAllocationTable->addColumn('rateId', 'integer');
        $rateAllocationTable->addColumn('routeId', 'integer', array('notnull' => false, 'default' => null));
        $rateAllocationTable->addColumn('appId', 'integer', array('notnull' => false, 'default' => null));
        $rateAllocationTable->addColumn('authenticated', 'integer', array('notnull' => false, 'default' => null));
        $rateAllocationTable->addColumn('parameters', 'string', array('length' => 255, 'notnull' => false, 'default' => null));
        $rateAllocationTable->setPrimaryKey(array('id'));

        $userTable = $schema->createTable('fusio_user');
        $userTable->addColumn('id', 'integer', array('autoincrement' => true));
        $userTable->addColumn('provider', 'integer', array('default' => ProviderInterface::PROVIDER_SYSTEM));
        $userTable->addColumn('status', 'integer');
        $userTable->addColumn('remoteId', 'string', array('length' => 255, 'notnull' => false, 'default' => null));
        $userTable->addColumn('name', 'string', array('length' => 64));
        $userTable->addColumn('email', 'string', array('length' => 128, 'notnull' => false, 'default' => null));
        $userTable->addColumn('password', 'string', array('length' => 255, 'notnull' => false, 'default' => null));
        $userTable->addColumn('date', 'datetime');
        $userTable->setPrimaryKey(array('id'));
        $userTable->addUniqueIndex(array('provider', 'remoteId'));
        $userTable->addUniqueIndex(array('name'));
        $userTable->addUniqueIndex(array('email'));

        $scopeRoutesTable = $schema->createTable('fusio_scope_routes');
        $scopeRoutesTable->addColumn('id', 'integer', array('autoincrement' => true));
        $scopeRoutesTable->addColumn('scopeId', 'integer');
        $scopeRoutesTable->addColumn('routeId', 'integer');
        $scopeRoutesTable->addColumn('allow', 'smallint');
        $scopeRoutesTable->addColumn('methods', 'string', array('length' => 64, 'notnull' => false));
        $scopeRoutesTable->setPrimaryKey(array('id'));

        $userGrantTable = $schema->createTable('fusio_user_grant');
        $userGrantTable->addColumn('id', 'integer', array('autoincrement' => true));
        $userGrantTable->addColumn('userId', 'integer');
        $userGrantTable->addColumn('appId', 'integer');
        $userGrantTable->addColumn('allow', 'integer');
        $userGrantTable->addColumn('date', 'datetime');
        $userGrantTable->setPrimaryKey(array('id'));
        $userGrantTable->addUniqueIndex(array('userId', 'appId'));

        $userScopeTable = $schema->createTable('fusio_user_scope');
        $userScopeTable->addColumn('id', 'integer', array('autoincrement' => true));
        $userScopeTable->addColumn('userId', 'integer');
        $userScopeTable->addColumn('scopeId', 'integer');
        $userScopeTable->setPrimaryKey(array('id'));
        $userScopeTable->addUniqueIndex(array('userId', 'scopeId'));

        $appTable->addForeignKeyConstraint($userTable, array('userId'), array('id'), array(), 'appUserId');

        $appScopeTable->addForeignKeyConstraint($appTable, array('appId'), array('id'), array(), 'appScopeAppId');
        $appScopeTable->addForeignKeyConstraint($scopeTable, array('scopeId'), array('id'), array(), 'appScopeScopeId');

        $appTokenTable->addForeignKeyConstraint($appTable, array('appId'), array('id'), array(), 'appTokenAppId');
        $appTokenTable->addForeignKeyConstraint($userTable, array('userId'), array('id'), array(), 'appTokenUserId');

        $routesMethodTable->addForeignKeyConstraint($routesTable, array('routeId'), array('id'), array(), 'routesMethodRouteId');
        $routesMethodTable->addForeignKeyConstraint($schemaTable, array('parameters'), array('id'), array(), 'routesMethodParameters');
        $routesMethodTable->addForeignKeyConstraint($schemaTable, array('request'), array('id'), array(), 'routesMethodRequest');
        $routesMethodTable->addForeignKeyConstraint($actionTable, array('action'), array('id'), array(), 'routesMethodAction');

        $routesResponseTable->addForeignKeyConstraint($routesMethodTable, array('methodId'), array('id'), array(), 'routesResponseMethodId');
        $routesResponseTable->addForeignKeyConstraint($schemaTable, array('response'), array('id'), array(), 'routesResponseResponse');

        $rateAllocationTable->addForeignKeyConstraint($rateTable, array('rateId'), array('id'), array(), 'rateAllocationRateId');
        $rateAllocationTable->addForeignKeyConstraint($routesTable, array('routeId'), array('id'), array(), 'rateAllocationRouteId');
        $rateAllocationTable->addForeignKeyConstraint($appTable, array('appId'), array('id'), array(), 'rateAllocationAppId');

        $scopeRoutesTable->addForeignKeyConstraint($scopeTable, array('scopeId'), array('id'), array(), 'scopeRoutesScopeId');
        $scopeRoutesTable->addForeignKeyConstraint($routesTable, array('routeId'), array('id'), array(), 'scopeRoutesRouteId');

        $userGrantTable->addForeignKeyConstraint($userTable, array('userId'), array('id'), array(), 'userGrantUserId');
        $userGrantTable->addForeignKeyConstraint($appTable, array('appId'), array('id'), array(), 'userGrantAppId');

        $userScopeTable->addForeignKeyConstraint($scopeTable, array('scopeId'), array('id'), array(), 'userScopeScopeId');
        $userScopeTable->addForeignKeyConstraint($userTable, array('userId'), array('id'), array(), 'userScopeUserId');

        return $schema;
    }

    public function executeInstall(Connection $connection)
    {
        $inserts = $this->getInstallInserts();

        foreach ($inserts as $tableName => $queries) {
            foreach ($queries as $data) {
                $connection->insert($tableName, $data);
            }
        }
    }

    public function executeUpgrade(Connection $connection)
    {
        $inserts = $this->getInstallInserts();

        foreach ($inserts['fusio_routes'] as $row) {
            $route = $connection->fetchAssoc('SELECT id, methods, path FROM fusio_routes WHERE controller = :controller', [
                'controller' => $row['controller']
            ]);

            if (empty($route)) {
                // if we insert a new route its important that we have not
                // already an old entry which uses this path. If this is the
                // case we need to deactivate the old entries
                $count = (int) $connection->fetchColumn('SELECT COUNT(id) AS cnt FROM fusio_routes WHERE path = :path', [
                    'path' => $row['path']
                ]);

                if ($count > 0) {
                    // deactivate all existing paths
                    $connection->executeUpdate('UPDATE fusio_routes SET status = :status WHERE path = :path', [
                        'status' => Table\Routes::STATUS_DELETED,
                        'path'   => $row['path'],
                    ]);
                }

                // insert new route
                $connection->insert('fusio_routes', $row);

                $routeId = $connection->lastInsertId();

                // insert scope
                $scopeId = $this->getScopeIdFromPath($row['path']);
                if ($scopeId !== null && !empty($routeId)) {
                    $connection->insert('fusio_scope_routes', [
                        'scopeId' => $scopeId,
                        'routeId' => $routeId,
                        'allow'   => 1,
                        'methods' => 'GET|POST|PUT|PATCH|DELETE',
                    ]);
                }
            } else {
                // the route already exists we must check whether something has
                // changed and update the route in case
                $changes = [];
                $keys    = ['methods', 'path'];

                foreach ($keys as $key) {
                    if ($row[$key] != $route[$key]) {
                        $changes[$key] = $row[$key];
                    }
                }

                if (!empty($changes)) {
                    $connection->update('fusio_routes', $changes, [
                        'id' => $route['id']
                    ]);
                }
            }
        }
    }

    public function getInstallInserts()
    {
        $backendAppKey     = TokenGenerator::generateAppKey();
        $backendAppSecret  = TokenGenerator::generateAppSecret();
        $consumerAppKey    = TokenGenerator::generateAppKey();
        $consumerAppSecret = TokenGenerator::generateAppSecret();
        $password          = \password_hash(TokenGenerator::generateUserPassword(), PASSWORD_DEFAULT);

        $parser = new Parser();
        $now    = new DateTime();
        $schema = $this->getPassthruSchema();
        $cache  = $parser->parse($schema);

        $data = [
            'fusio_user' => [
                ['status' => 1, 'name' => 'Administrator', 'email' => 'admin@localhost.com', 'password' => $password, 'date' => $now->format('Y-m-d H:i:s')],
            ],
            'fusio_app' => [
                ['userId' => 1, 'status' => 1, 'name' => 'Backend',  'url' => 'http://fusio-project.org', 'parameters' => '', 'appKey' => $backendAppKey, 'appSecret' => $backendAppSecret, 'date' => $now->format('Y-m-d H:i:s')],
                ['userId' => 1, 'status' => 1, 'name' => 'Consumer', 'url' => 'http://fusio-project.org', 'parameters' => '', 'appKey' => $consumerAppKey, 'appSecret' => $consumerAppSecret, 'date' => $now->format('Y-m-d H:i:s')],
            ],
            'fusio_config' => [
                ['name' => 'app_approval', 'type' => Table\Config::FORM_BOOLEAN, 'description' => 'If true the status of a new app is PENDING so that an administrator has to manually activate the app', 'value' => 0],
                ['name' => 'app_consumer', 'type' => Table\Config::FORM_NUMBER, 'description' => 'The max amount of apps a consumer can register', 'value' => 16],
                ['name' => 'scopes_default', 'type' => Table\Config::FORM_STRING, 'description' => 'If a user registers through the consumer API the following scopes are assigned', 'value' => 'authorization,consumer'],
                ['name' => 'mail_register_subject', 'type' => Table\Config::FORM_STRING, 'description' => 'Subject of the activation mail', 'value' => 'Fusio registration'],
                ['name' => 'mail_register_body', 'type' => Table\Config::FORM_TEXT, 'description' => 'Body of the activation mail', 'value' => 'Hello {name},' . "\n\n" . 'you have successful registered at Fusio.' . "\n" . 'To activate you account please visit the following link:' . "\n" . 'http://127.0.0.1/projects/fusio/public/consumer/#activate?token={token}'],
                ['name' => 'mail_sender', 'type' => Table\Config::FORM_STRING, 'description' => 'Email address which is used in the "From" header', 'value' => ''],
                ['name' => 'provider_facebook_secret', 'type' => Table\Config::FORM_STRING, 'description' => 'Facebook app secret', 'value' => ''],
                ['name' => 'provider_google_secret', 'type' => Table\Config::FORM_STRING, 'description' => 'Google app secret', 'value' => ''],
                ['name' => 'provider_github_secret', 'type' => Table\Config::FORM_STRING, 'description' => 'GitHub app secret', 'value' => ''],
                ['name' => 'recaptcha_secret', 'type' => Table\Config::FORM_STRING, 'description' => 'ReCaptcha secret', 'value' => ''],
                ['name' => 'cors_allow_origin', 'type' => Table\Config::FORM_STRING, 'description' => 'If set each API response contains a Access-Control-Allow-Origin header with the provided value', 'value' => ''],
                ['name' => 'user_pw_length', 'type' => Table\Config::FORM_NUMBER, 'description' => 'Minimal required password length', 'value' => 8],
            ],
            'fusio_connection' => [
            ],
            'fusio_audit' => [
            ],
            'fusio_connection_class' => [
                ['class' => Adapter\Http\Connection\Http::class],
                ['class' => Adapter\Sql\Connection\Sql::class],
                ['class' => Adapter\Sql\Connection\SqlAdvanced::class],
            ],
            'fusio_scope' => [
                ['name' => 'backend', 'description' => 'Access to the backend API'],
                ['name' => 'consumer', 'description' => 'Consumer API endpoint'],
                ['name' => 'authorization', 'description' => 'Authorization API endpoint'],
            ],
            'fusio_action' => [
                ['status' => 1, 'name' => 'Welcome', 'class' => Welcome::class, 'engine' => PhpClass::class, 'config' => null, 'date' => $now->format('Y-m-d H:i:s')],
            ],
            'fusio_action_class' => [
                ['class' => Adapter\Http\Action\HttpProcessor::class],
                ['class' => Adapter\Php\Action\PhpProcessor::class],
                ['class' => Adapter\Sql\Action\SqlTable::class],
                ['class' => Adapter\Util\Action\UtilStaticResponse::class],
                ['class' => Adapter\V8\Action\V8Processor::class],
                ['class' => Adapter\File\Action\FileProcessor::class],
            ],
            'fusio_schema' => [
                ['status' => 1, 'name' => 'Passthru', 'source' => $schema, 'cache' => $cache]
            ],
            'fusio_rate' => [
                ['status' => 1, 'priority' => 0, 'name' => 'Default', 'rateLimit' => 720, 'timespan' => 'PT1H'],
                ['status' => 1, 'priority' => 4, 'name' => 'Default-Anonymous', 'rateLimit' => 60, 'timespan' => 'PT1H'],
            ],
            'fusio_routes' => [
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/action',                              'controller' => Backend\Api\Action\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/action/list',                         'controller' => Backend\Api\Action\Index::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/action/form',                         'controller' => Backend\Api\Action\Form::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/action/execute/$action_id<[0-9]+>',   'controller' => Backend\Api\Action\Execute::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/action/$action_id<[0-9]+>',           'controller' => Backend\Api\Action\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/app/token',                           'controller' => Backend\Api\App\Token\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/app/token/$token_id<[0-9]+>',         'controller' => Backend\Api\App\Token\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/app',                                 'controller' => Backend\Api\App\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/app/$app_id<[0-9]+>',                 'controller' => Backend\Api\App\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/app/$app_id<[0-9]+>/token/:token_id', 'controller' => Backend\Api\App\Token::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/audit',                               'controller' => Backend\Api\Audit\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/audit/$audit_id<[0-9]+>',             'controller' => Backend\Api\Audit\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/config',                              'controller' => Backend\Api\Config\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/config/$config_id<[0-9]+>',           'controller' => Backend\Api\Config\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/connection',                          'controller' => Backend\Api\Connection\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/connection/list',                     'controller' => Backend\Api\Connection\Index::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/connection/form',                     'controller' => Backend\Api\Connection\Form::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/connection/$connection_id<[0-9]+>',   'controller' => Backend\Api\Connection\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/cronjob',                             'controller' => Backend\Api\Cronjob\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/cronjob/$cronjob_id<[0-9]+>',         'controller' => Backend\Api\Cronjob\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/log/error',                           'controller' => Backend\Api\Log\Error\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/log/error/$error_id<[0-9]+>',         'controller' => Backend\Api\Log\Error\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/log',                                 'controller' => Backend\Api\Log\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/log/$log_id<[0-9]+>',                 'controller' => Backend\Api\Log\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/rate',                                'controller' => Backend\Api\Rate\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/rate/$rate_id<[0-9]+>',               'controller' => Backend\Api\Rate\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/routes',                              'controller' => Backend\Api\Routes\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/routes/$route_id<[0-9]+>',            'controller' => Backend\Api\Routes\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/schema',                              'controller' => Backend\Api\Schema\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/schema/preview/$schema_id<[0-9]+>',   'controller' => Backend\Api\Schema\Preview::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/schema/$schema_id<[0-9]+>',           'controller' => Backend\Api\Schema\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/scope',                               'controller' => Backend\Api\Scope\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/scope/$scope_id<[0-9]+>',             'controller' => Backend\Api\Scope\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/user',                                'controller' => Backend\Api\User\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/user/$user_id<[0-9]+>',               'controller' => Backend\Api\User\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/dashboard',                           'controller' => Backend\Api\Dashboard\Dashboard::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/statistic/incoming_requests',         'controller' => Backend\Api\Statistic\IncomingRequests::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/statistic/most_used_routes',          'controller' => Backend\Api\Statistic\MostUsedRoutes::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/statistic/most_used_apps',            'controller' => Backend\Api\Statistic\MostUsedApps::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/statistic/errors_per_route',          'controller' => Backend\Api\Statistic\ErrorsPerRoute::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/statistic/issued_tokens',             'controller' => Backend\Api\Statistic\IssuedTokens::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/statistic/count_requests',            'controller' => Backend\Api\Statistic\CountRequests::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/statistic/time_average',              'controller' => Backend\Api\Statistic\TimeAverage::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/statistic/time_per_route',            'controller' => Backend\Api\Statistic\TimePerRoute::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/account/change_password',             'controller' => Backend\Api\Account\ChangePassword::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/import/process',                      'controller' => Backend\Api\Import\Process::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/import/:format',                      'controller' => Backend\Api\Import\Format::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/backend/token',                               'controller' => Backend\Authorization\Token::class],

                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/app/developer',                      'controller' => Consumer\Api\App\Developer\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/app/developer/$app_id<[0-9]+>',      'controller' => Consumer\Api\App\Developer\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/app/grant',                          'controller' => Consumer\Api\App\Grant\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/app/grant/$grant_id<[0-9]+>',        'controller' => Consumer\Api\App\Grant\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/app/meta',                           'controller' => Consumer\Api\App\Meta\Entity::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/scope',                              'controller' => Consumer\Api\Scope\Collection::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/token',                              'controller' => Consumer\Authorization\Token::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/authorize',                          'controller' => Consumer\Api\User\Authorize::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/login',                              'controller' => Consumer\Api\User\Login::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/register',                           'controller' => Consumer\Api\User\Register::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/provider/:provider',                 'controller' => Consumer\Api\User\Provider::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/activate',                           'controller' => Consumer\Api\User\Activate::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/account',                            'controller' => Consumer\Api\User\Account::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/consumer/account/change_password',            'controller' => Consumer\Api\User\ChangePassword::class],

                ['status' => 1, 'methods' => 'ANY', 'path' => '/authorization/revoke',                        'controller' => Authorization\Revoke::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/authorization/token',                         'controller' => Authorization\Token::class],
                ['status' => 1, 'methods' => 'ANY', 'path' => '/authorization/whoami',                        'controller' => Authorization\Whoami::class],

                ['status' => 1, 'methods' => 'GET', 'path' => '/doc',                                         'controller' => Tool\DocumentationController::class . '::doIndex'],
                ['status' => 1, 'methods' => 'GET', 'path' => '/doc/:version/*path',                          'controller' => Tool\DocumentationController::class . '::doDetail'],

                ['status' => 1, 'methods' => 'GET', 'path' => '/export/wsdl/:version/*path',                  'controller' => Backend\Api\Gone::class],
                ['status' => 1, 'methods' => 'GET', 'path' => '/export/raml/:version/*path',                  'controller' => Generator\RamlController::class],
                ['status' => 1, 'methods' => 'GET', 'path' => '/export/swagger/:version/*path',               'controller' => Generator\SwaggerController::class],
                ['status' => 1, 'methods' => 'GET', 'path' => '/export/openapi/:version/*path',               'controller' => Generator\OpenAPIController::class],

                ['status' => 1, 'methods' => 'ANY', 'path' => '/',                                            'controller' => SchemaApiController::class],
            ],
            'fusio_rate_allocation' => [
                ['rateId' => 1, 'routeId' => null, 'appId' => null, 'authenticated' => null, 'parameters' => null],
                ['rateId' => 2, 'routeId' => null, 'appId' => null, 'authenticated' => 0, 'parameters' => null],
            ],
            'fusio_app_scope' => [
                ['appId' => 1, 'scopeId' => 1],
                ['appId' => 1, 'scopeId' => 3],
                ['appId' => 2, 'scopeId' => 2],
                ['appId' => 2, 'scopeId' => 3],
            ],
            'fusio_user_scope' => [
                ['userId' => 1, 'scopeId' => 1],
                ['userId' => 1, 'scopeId' => 2],
                ['userId' => 1, 'scopeId' => 3],
            ],
        ];

        // routes method
        $lastRouteId = count($data['fusio_routes']);
        $data['fusio_routes_method'] = [
            ['routeId' => $lastRouteId, 'method' => 'GET', 'version' => 1, 'status' => Resource::STATUS_DEVELOPMENT, 'active' => 1, 'public' => 1, 'parameters' => null, 'request' => null, 'action' => 1],
        ];

        $data['fusio_routes_response'] = [
            ['methodId' => 1, 'code' => 200, 'response' => 1],
        ];

        // scope routes
        $data['fusio_scope_routes'] = [];
        foreach ($data['fusio_routes'] as $index => $row) {
            $scopeId = $this->getScopeIdFromPath($row['path']);
            if ($scopeId !== null) {
                $data['fusio_scope_routes'][] = ['scopeId' => $scopeId, 'routeId' => $index + 1, 'allow' => 1, 'methods' => 'GET|POST|PUT|PATCH|DELETE'];
            }
        }

        return $data;
    }

    private function getPassthruSchema()
    {
        return json_encode([
            'id' => 'http://fusio-project.org',
            'title' => 'passthru',
            'type' => 'object',
            'description' => 'No schema was specified.',
            'properties' => new \stdClass(),
        ], JSON_PRETTY_PRINT);
    }

    private function getScopeIdFromPath($path)
    {
        if (strpos($path, '/backend') === 0) {
            return 1;
        } elseif (strpos($path, '/consumer') === 0) {
            return 2;
        } elseif (strpos($path, '/authorization') === 0) {
            return 3;
        }

        return null;
    }
}

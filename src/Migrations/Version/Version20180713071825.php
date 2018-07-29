<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Fusio\Adapter;
use Fusio\Impl\Backend;
use Fusio\Impl\Consumer;
use Fusio\Impl\Export;
use Fusio\Impl\Service\User\ProviderInterface;
use Fusio\Impl\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180713071825 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        if (!$schema->hasTable('fusio_action')) {
            $actionTable = $schema->createTable('fusio_action');
            $actionTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $actionTable->addColumn('status', 'integer', ['default' => Table\Action::STATUS_ACTIVE]);
            $actionTable->addColumn('name', 'string', ['length' => 255]);
            $actionTable->addColumn('class', 'string', ['length' => 255]);
            $actionTable->addColumn('engine', 'string', ['length' => 255, 'notnull' => false]);
            $actionTable->addColumn('config', 'text', ['notnull' => false]);
            $actionTable->addColumn('date', 'datetime');
            $actionTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_action_class')) {
            $actionClassTable = $schema->createTable('fusio_action_class');
            $actionClassTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $actionClassTable->addColumn('class', 'string', ['length' => 255]);
            $actionClassTable->setPrimaryKey(['id']);
            $actionClassTable->addUniqueIndex(['class']);
        }

        if (!$schema->hasTable('fusio_app')) {
            $appTable = $schema->createTable('fusio_app');
            $appTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $appTable->addColumn('user_id', 'integer');
            $appTable->addColumn('status', 'integer');
            $appTable->addColumn('name', 'string', ['length' => 64]);
            $appTable->addColumn('url', 'string', ['length' => 255]);
            $appTable->addColumn('parameters', 'string', ['length' => 255, 'notnull' => false]);
            $appTable->addColumn('app_key', 'string', ['length' => 255]);
            $appTable->addColumn('app_secret', 'string', ['length' => 255]);
            $appTable->addColumn('date', 'datetime');
            $appTable->setPrimaryKey(['id']);
            $appTable->addUniqueIndex(['app_key']);
        }

        if (!$schema->hasTable('fusio_app_scope')) {
            $appScopeTable = $schema->createTable('fusio_app_scope');
            $appScopeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $appScopeTable->addColumn('app_id', 'integer');
            $appScopeTable->addColumn('scope_id', 'integer');
            $appScopeTable->setPrimaryKey(['id']);
            $appScopeTable->addUniqueIndex(['app_id', 'scope_id']);
        }

        if (!$schema->hasTable('fusio_app_token')) {
            $appTokenTable = $schema->createTable('fusio_app_token');
            $appTokenTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $appTokenTable->addColumn('app_id', 'integer');
            $appTokenTable->addColumn('user_id', 'integer');
            $appTokenTable->addColumn('status', 'integer', ['default' => 1]);
            $appTokenTable->addColumn('token', 'string', ['length' => 255]);
            $appTokenTable->addColumn('refresh', 'string', ['length' => 255, 'notnull' => false]);
            $appTokenTable->addColumn('scope', 'string', ['length' => 255]);
            $appTokenTable->addColumn('ip', 'string', ['length' => 40]);
            $appTokenTable->addColumn('expire', 'datetime', ['notnull' => false]);
            $appTokenTable->addColumn('date', 'datetime');
            $appTokenTable->setPrimaryKey(['id']);
            $appTokenTable->addUniqueIndex(['token']);
        }

        if (!$schema->hasTable('fusio_app_code')) {
            $appCodeTable = $schema->createTable('fusio_app_code');
            $appCodeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $appCodeTable->addColumn('app_id', 'integer');
            $appCodeTable->addColumn('user_id', 'integer');
            $appCodeTable->addColumn('code', 'string', ['length' => 255]);
            $appCodeTable->addColumn('redirect_uri', 'string', ['length' => 255, 'notnull' => false]);
            $appCodeTable->addColumn('scope', 'string', ['length' => 255]);
            $appCodeTable->addColumn('date', 'datetime');
            $appCodeTable->setPrimaryKey(['id']);
            $appCodeTable->addUniqueIndex(['code']);
        }

        if (!$schema->hasTable('fusio_audit')) {
            $auditTable = $schema->createTable('fusio_audit');
            $auditTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $auditTable->addColumn('app_id', 'integer');
            $auditTable->addColumn('user_id', 'integer');
            $auditTable->addColumn('ref_id', 'integer', ['notnull' => false]);
            $auditTable->addColumn('event', 'string');
            $auditTable->addColumn('ip', 'string', ['length' => 40]);
            $auditTable->addColumn('message', 'string');
            $auditTable->addColumn('content', 'text', ['notnull' => false]);
            $auditTable->addColumn('date', 'datetime');
            $auditTable->setPrimaryKey(['id']);
            $auditTable->addOption('engine', 'MyISAM');
        }

        if (!$schema->hasTable('fusio_config')) {
            $configTable = $schema->createTable('fusio_config');
            $configTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $configTable->addColumn('type', 'integer', ['default' => 1]);
            $configTable->addColumn('name', 'string', ['length' => 64]);
            $configTable->addColumn('description', 'string', ['length' => 255]);
            $configTable->addColumn('value', 'string');
            $configTable->setPrimaryKey(['id']);
            $configTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_connection')) {
            $connectionTable = $schema->createTable('fusio_connection');
            $connectionTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $connectionTable->addColumn('status', 'integer', ['default' => Table\Connection::STATUS_ACTIVE]);
            $connectionTable->addColumn('name', 'string', ['length' => 255]);
            $connectionTable->addColumn('class', 'string', ['length' => 255]);
            $connectionTable->addColumn('config', 'text', ['notnull' => false]);
            $connectionTable->setPrimaryKey(['id']);
            $connectionTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_connection_class')) {
            $connectionClassTable = $schema->createTable('fusio_connection_class');
            $connectionClassTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $connectionClassTable->addColumn('class', 'string', ['length' => 255]);
            $connectionClassTable->setPrimaryKey(['id']);
            $connectionClassTable->addUniqueIndex(['class']);
        }

        if (!$schema->hasTable('fusio_cronjob')) {
            $cronjobTable = $schema->createTable('fusio_cronjob');
            $cronjobTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $cronjobTable->addColumn('status', 'integer', ['default' => Table\Cronjob::STATUS_ACTIVE]);
            $cronjobTable->addColumn('name', 'string', ['length' => 64]);
            $cronjobTable->addColumn('cron', 'string');
            $cronjobTable->addColumn('action', 'integer', ['notnull' => false]);
            $cronjobTable->addColumn('execute_date', 'datetime', ['notnull' => false]);
            $cronjobTable->addColumn('exit_code', 'integer', ['notnull' => false]);
            $cronjobTable->setPrimaryKey(['id']);
            $cronjobTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_cronjob_error')) {
            $cronjobErrorTable = $schema->createTable('fusio_cronjob_error');
            $cronjobErrorTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $cronjobErrorTable->addColumn('cronjob_id', 'integer');
            $cronjobErrorTable->addColumn('message', 'string', ['length' => 500]);
            $cronjobErrorTable->addColumn('trace', 'text');
            $cronjobErrorTable->addColumn('file', 'string', ['length' => 255]);
            $cronjobErrorTable->addColumn('line', 'integer');
            $cronjobErrorTable->setPrimaryKey(['id']);
            $cronjobErrorTable->addOption('engine', 'MyISAM');
        }

        if (!$schema->hasTable('fusio_event')) {
            $eventTable = $schema->createTable('fusio_event');
            $eventTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $eventTable->addColumn('status', 'integer');
            $eventTable->addColumn('name', 'string', ['length' => 64]);
            $eventTable->addColumn('description', 'string', ['length' => 255]);
            $eventTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_event_response')) {
            $eventResponseTable = $schema->createTable('fusio_event_response');
            $eventResponseTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $eventResponseTable->addColumn('trigger_id', 'integer');
            $eventResponseTable->addColumn('subscription_id', 'integer');
            $eventResponseTable->addColumn('status', 'integer');
            $eventResponseTable->addColumn('code', 'integer', ['notnull' => false]);
            $eventResponseTable->addColumn('attempts', 'integer');
            $eventResponseTable->addColumn('execute_date', 'datetime', ['notnull' => false]);
            $eventResponseTable->addColumn('insert_date', 'datetime');
            $eventResponseTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_event_subscription')) {
            $eventSubscriptionTable = $schema->createTable('fusio_event_subscription');
            $eventSubscriptionTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $eventSubscriptionTable->addColumn('event_id', 'integer');
            $eventSubscriptionTable->addColumn('user_id', 'integer');
            $eventSubscriptionTable->addColumn('status', 'integer');
            $eventSubscriptionTable->addColumn('endpoint', 'string', ['length' => 255]);
            $eventSubscriptionTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_event_trigger')) {
            $eventTriggerTable = $schema->createTable('fusio_event_trigger');
            $eventTriggerTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $eventTriggerTable->addColumn('event_id', 'integer');
            $eventTriggerTable->addColumn('status', 'integer');
            $eventTriggerTable->addColumn('payload', 'text');
            $eventTriggerTable->addColumn('insert_date', 'datetime');
            $eventTriggerTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_log')) {
            $logTable = $schema->createTable('fusio_log');
            $logTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $logTable->addColumn('route_id', 'integer', ['notnull' => false]);
            $logTable->addColumn('app_id', 'integer', ['notnull' => false]);
            $logTable->addColumn('user_id', 'integer', ['notnull' => false]);
            $logTable->addColumn('ip', 'string', ['length' => 40]);
            $logTable->addColumn('user_agent', 'string', ['length' => 255]);
            $logTable->addColumn('method', 'string', ['length' => 16]);
            $logTable->addColumn('path', 'string', ['length' => 1023]);
            $logTable->addColumn('header', 'text');
            $logTable->addColumn('body', 'text', ['notnull' => false]);
            $logTable->addColumn('execution_time', 'integer', ['notnull' => false, 'default' => null]);
            $logTable->addColumn('date', 'datetime');
            $logTable->setPrimaryKey(['id']);
            $logTable->addOption('engine', 'MyISAM');
        }

        if (!$schema->hasTable('fusio_log_error')) {
            $logErrorTable = $schema->createTable('fusio_log_error');
            $logErrorTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $logErrorTable->addColumn('log_id', 'integer');
            $logErrorTable->addColumn('message', 'string', ['length' => 500]);
            $logErrorTable->addColumn('trace', 'text');
            $logErrorTable->addColumn('file', 'string', ['length' => 255]);
            $logErrorTable->addColumn('line', 'integer');
            $logErrorTable->setPrimaryKey(['id']);
            $logErrorTable->addOption('engine', 'MyISAM');
        }

        if (!$schema->hasTable('fusio_routes')) {
            $routesTable = $schema->createTable('fusio_routes');
            $routesTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $routesTable->addColumn('status', 'integer', ['default' => Table\Routes::STATUS_ACTIVE]);
            $routesTable->addColumn('priority', 'integer', ['notnull' => false]);
            $routesTable->addColumn('methods', 'string', ['length' => 64]);
            $routesTable->addColumn('path', 'string', ['length' => 255]);
            $routesTable->addColumn('controller', 'string', ['length' => 255]);
            $routesTable->setPrimaryKey(['id']);
            $routesTable->addIndex(['priority']);
        }

        if (!$schema->hasTable('fusio_routes_method')) {
            $routesMethodTable = $schema->createTable('fusio_routes_method');
            $routesMethodTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $routesMethodTable->addColumn('route_id', 'integer');
            $routesMethodTable->addColumn('method', 'string', ['length' => 8]);
            $routesMethodTable->addColumn('version', 'integer');
            $routesMethodTable->addColumn('status', 'integer');
            $routesMethodTable->addColumn('active', 'integer', ['default' => 0]);
            $routesMethodTable->addColumn('public', 'integer', ['default' => 0]);
            $routesMethodTable->addColumn('description', 'string', ['notnull' => false, 'length' => 500]);
            $routesMethodTable->addColumn('parameters', 'integer', ['notnull' => false]);
            $routesMethodTable->addColumn('request', 'integer', ['notnull' => false]);
            $routesMethodTable->addColumn('action', 'integer', ['notnull' => false]);
            $routesMethodTable->addColumn('schema_cache', 'text', ['notnull' => false]);
            $routesMethodTable->addColumn('action_cache', 'text', ['notnull' => false]);
            $routesMethodTable->setPrimaryKey(['id']);
            $routesMethodTable->addUniqueIndex(['route_id', 'method', 'version']);
        }

        if (!$schema->hasTable('fusio_routes_response')) {
            $routesResponseTable = $schema->createTable('fusio_routes_response');
            $routesResponseTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $routesResponseTable->addColumn('method_id', 'integer');
            $routesResponseTable->addColumn('code', 'smallint');
            $routesResponseTable->addColumn('response', 'integer');
            $routesResponseTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_schema')) {
            $schemaTable = $schema->createTable('fusio_schema');
            $schemaTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $schemaTable->addColumn('status', 'integer', ['default' => Table\Schema::STATUS_ACTIVE]);
            $schemaTable->addColumn('name', 'string', ['length' => 255]);
            $schemaTable->addColumn('source', 'text');
            $schemaTable->addColumn('cache', 'text');
            $schemaTable->addColumn('form', 'text', ['notnull' => false, 'default' => null]);
            $schemaTable->setPrimaryKey(['id']);
            $schemaTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_scope')) {
            $scopeTable = $schema->createTable('fusio_scope');
            $scopeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $scopeTable->addColumn('name', 'string', ['length' => 32]);
            $scopeTable->addColumn('description', 'string', ['length' => 255]);
            $scopeTable->setPrimaryKey(['id']);
            $scopeTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_rate')) {
            $rateTable = $schema->createTable('fusio_rate');
            $rateTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $rateTable->addColumn('status', 'integer');
            $rateTable->addColumn('priority', 'integer');
            $rateTable->addColumn('name', 'string', ['length' => 64]);
            $rateTable->addColumn('rate_limit', 'integer');
            $rateTable->addColumn('timespan', 'string');
            $rateTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_rate_allocation')) {
            $rateAllocationTable = $schema->createTable('fusio_rate_allocation');
            $rateAllocationTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $rateAllocationTable->addColumn('rate_id', 'integer');
            $rateAllocationTable->addColumn('route_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('app_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('authenticated', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('parameters', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
            $rateAllocationTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_user')) {
            $userTable = $schema->createTable('fusio_user');
            $userTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $userTable->addColumn('provider', 'integer', ['default' => ProviderInterface::PROVIDER_SYSTEM]);
            $userTable->addColumn('status', 'integer');
            $userTable->addColumn('remote_id', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('name', 'string', ['length' => 64]);
            $userTable->addColumn('email', 'string', ['length' => 128, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('password', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('date', 'datetime');
            $userTable->setPrimaryKey(['id']);
            $userTable->addUniqueIndex(['provider', 'remote_id']);
            $userTable->addUniqueIndex(['name']);
            $userTable->addUniqueIndex(['email']);
        }

        if (!$schema->hasTable('fusio_scope_routes')) {
            $scopeRoutesTable = $schema->createTable('fusio_scope_routes');
            $scopeRoutesTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $scopeRoutesTable->addColumn('scope_id', 'integer');
            $scopeRoutesTable->addColumn('route_id', 'integer');
            $scopeRoutesTable->addColumn('allow', 'smallint');
            $scopeRoutesTable->addColumn('methods', 'string', ['length' => 64, 'notnull' => false]);
            $scopeRoutesTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_user_grant')) {
            $userGrantTable = $schema->createTable('fusio_user_grant');
            $userGrantTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $userGrantTable->addColumn('user_id', 'integer');
            $userGrantTable->addColumn('app_id', 'integer');
            $userGrantTable->addColumn('allow', 'integer');
            $userGrantTable->addColumn('date', 'datetime');
            $userGrantTable->setPrimaryKey(['id']);
            $userGrantTable->addUniqueIndex(['user_id', 'app_id']);
        }

        if (!$schema->hasTable('fusio_user_scope')) {
            $userScopeTable = $schema->createTable('fusio_user_scope');
            $userScopeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $userScopeTable->addColumn('user_id', 'integer');
            $userScopeTable->addColumn('scope_id', 'integer');
            $userScopeTable->setPrimaryKey(['id']);
            $userScopeTable->addUniqueIndex(['user_id', 'scope_id']);
        }

        if (isset($appTable)) {
            $appTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'app_user_id');
        }

        if (isset($appScopeTable)) {
            $appScopeTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'app_scope_app_id');
            $appScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'app_scope_scope_id');
        }

        if (isset($appTokenTable)) {
            $appTokenTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'app_token_app_id');
            $appTokenTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'app_token_user_id');
        }

        if (isset($eventResponseTable)) {
            $eventResponseTable->addForeignKeyConstraint($schema->getTable('fusio_event_trigger'), ['trigger_id'], ['id'], [], 'event_response_trigger_id');
            $eventResponseTable->addForeignKeyConstraint($schema->getTable('fusio_event_subscription'), ['subscription_id'], ['id'], [], 'event_response_subscription_id');
        }

        if (isset($eventSubscriptionTable)) {
            $eventSubscriptionTable->addForeignKeyConstraint($schema->getTable('fusio_event'), ['event_id'], ['id'], [], 'event_subscription_event_id');
            $eventSubscriptionTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'event_subscription_user_id');
        }

        if (isset($eventTriggerTable)) {
            $eventTriggerTable->addForeignKeyConstraint($schema->getTable('fusio_event'), ['event_id'], ['id'], [], 'event_trigger_event_id');
        }

        if (isset($routesMethodTable)) {
            $routesMethodTable->addForeignKeyConstraint($schema->getTable('fusio_routes'), ['route_id'], ['id'], [], 'routes_method_route_id');
            $routesMethodTable->addForeignKeyConstraint($schema->getTable('fusio_schema'), ['parameters'], ['id'], [], 'routes_method_parameters');
            $routesMethodTable->addForeignKeyConstraint($schema->getTable('fusio_schema'), ['request'], ['id'], [], 'routes_method_request');
            $routesMethodTable->addForeignKeyConstraint($schema->getTable('fusio_action'), ['action'], ['id'], [], 'routes_method_action');
        }

        if (isset($routesResponseTable)) {
            $routesResponseTable->addForeignKeyConstraint($schema->getTable('fusio_routes_method'), ['method_id'], ['id'], [], 'routes_response_method_id');
            $routesResponseTable->addForeignKeyConstraint($schema->getTable('fusio_schema'), ['response'], ['id'], [], 'routes_response_response');
        }

        if (isset($rateAllocationTable)) {
            $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_rate'), ['rate_id'], ['id'], [], 'rate_allocation_rate_id');
            $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_routes'), ['route_id'], ['id'], [], 'rate_allocation_route_id');
            $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'rate_allocation_app_id');
        }

        if (isset($scopeRoutesTable)) {
            $scopeRoutesTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'scope_routes_scope_id');
            $scopeRoutesTable->addForeignKeyConstraint($schema->getTable('fusio_routes'), ['route_id'], ['id'], [], 'scope_routes_route_id');
        }

        if (isset($userGrantTable)) {
            $userGrantTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'user_grant_user_id');
            $userGrantTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'user_grant_app_id');
        }

        if (isset($userScopeTable)) {
            $userScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'user_scope_scope_id');
            $userScopeTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'user_scope_user_id');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('fusio_action');
        $schema->dropTable('fusio_action_class');
        $schema->dropTable('fusio_app');
        $schema->dropTable('fusio_app_code');
        $schema->dropTable('fusio_app_scope');
        $schema->dropTable('fusio_app_token');
        $schema->dropTable('fusio_audit');
        $schema->dropTable('fusio_config');
        $schema->dropTable('fusio_connection');
        $schema->dropTable('fusio_connection_class');
        $schema->dropTable('fusio_cronjob');
        $schema->dropTable('fusio_cronjob_error');
        $schema->dropTable('fusio_event');
        $schema->dropTable('fusio_event_response');
        $schema->dropTable('fusio_event_subscription');
        $schema->dropTable('fusio_event_trigger');
        $schema->dropTable('fusio_log');
        $schema->dropTable('fusio_log_error');
        $schema->dropTable('fusio_rate');
        $schema->dropTable('fusio_rate_allocation');
        $schema->dropTable('fusio_routes');
        $schema->dropTable('fusio_routes_method');
        $schema->dropTable('fusio_routes_response');
        $schema->dropTable('fusio_schema');
        $schema->dropTable('fusio_scope');
        $schema->dropTable('fusio_scope_routes');
        $schema->dropTable('fusio_user');
        $schema->dropTable('fusio_user_grant');
        $schema->dropTable('fusio_user_scope');
    }
}

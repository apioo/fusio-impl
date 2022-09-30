<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Migrations\DataSyncronizer;
use Fusio\Impl\Migrations\NewInstallation;
use Fusio\Impl\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200905081453 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Setup initial tables';
    }

    public function up(Schema $schema) : void
    {
        if (!$schema->hasTable('fusio_action')) {
            $actionTable = $schema->createTable('fusio_action');
            $actionTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $actionTable->addColumn('category_id', 'integer', ['default' => 1]);
            $actionTable->addColumn('status', 'integer', ['default' => Table\Action::STATUS_ACTIVE]);
            $actionTable->addColumn('name', 'string', ['length' => 255]);
            $actionTable->addColumn('class', 'string', ['length' => 255]);
            $actionTable->addColumn('async', 'boolean', ['default' => false]);
            $actionTable->addColumn('engine', 'string', ['length' => 255, 'notnull' => false]);
            $actionTable->addColumn('config', 'text', ['notnull' => false]);
            $actionTable->addColumn('date', 'datetime');
            $actionTable->setPrimaryKey(['id']);
            $actionTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_action_queue')) {
            $actionQueueTable = $schema->createTable('fusio_action_queue');
            $actionQueueTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $actionQueueTable->addColumn('action', 'string', ['length' => 255]);
            $actionQueueTable->addColumn('request', 'text');
            $actionQueueTable->addColumn('context', 'text');
            $actionQueueTable->addColumn('date', 'datetime');
            $actionQueueTable->setPrimaryKey(['id']);
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
            $appTokenTable->addColumn('token', 'string', ['length' => 512]);
            $appTokenTable->addColumn('refresh', 'string', ['length' => 255, 'notnull' => false]);
            $appTokenTable->addColumn('scope', 'string', ['length' => 1023]);
            $appTokenTable->addColumn('ip', 'string', ['length' => 40]);
            $appTokenTable->addColumn('expire', 'datetime', ['notnull' => false]);
            $appTokenTable->addColumn('date', 'datetime');
            $appTokenTable->setPrimaryKey(['id']);
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
        }

        if (!$schema->hasTable('fusio_category')) {
            $categoryTable = $schema->createTable('fusio_category');
            $categoryTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $categoryTable->addColumn('status', 'integer');
            $categoryTable->addColumn('name', 'string', ['length' => 64]);
            $categoryTable->setPrimaryKey(['id']);
            $categoryTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_config')) {
            $configTable = $schema->createTable('fusio_config');
            $configTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $configTable->addColumn('type', 'integer', ['default' => 1]);
            $configTable->addColumn('name', 'string', ['length' => 64]);
            $configTable->addColumn('description', 'string', ['length' => 255]);
            $configTable->addColumn('value', 'string', ['length' => 512]);
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

        if (!$schema->hasTable('fusio_cronjob')) {
            $cronjobTable = $schema->createTable('fusio_cronjob');
            $cronjobTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $cronjobTable->addColumn('category_id', 'integer', ['default' => 1]);
            $cronjobTable->addColumn('status', 'integer', ['default' => Table\Cronjob::STATUS_ACTIVE]);
            $cronjobTable->addColumn('name', 'string', ['length' => 64]);
            $cronjobTable->addColumn('cron', 'string');
            $cronjobTable->addColumn('action', 'string', ['notnull' => false]);
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
        }

        if (!$schema->hasTable('fusio_event')) {
            $eventTable = $schema->createTable('fusio_event');
            $eventTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $eventTable->addColumn('category_id', 'integer', ['default' => 1]);
            $eventTable->addColumn('status', 'integer');
            $eventTable->addColumn('name', 'string', ['length' => 64]);
            $eventTable->addColumn('description', 'string', ['length' => 255]);
            $eventTable->addColumn('event_schema', 'string', ['notnull' => false]);
            $eventTable->setPrimaryKey(['id']);
            $eventTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_event_response')) {
            $eventResponseTable = $schema->createTable('fusio_event_response');
            $eventResponseTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $eventResponseTable->addColumn('trigger_id', 'integer');
            $eventResponseTable->addColumn('subscription_id', 'integer');
            $eventResponseTable->addColumn('status', 'integer');
            $eventResponseTable->addColumn('code', 'integer', ['notnull' => false]);
            $eventResponseTable->addColumn('error', 'string', ['notnull' => false]);
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
            $logTable->addColumn('category_id', 'integer', ['default' => 1]);
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
            $logTable->addIndex(['category_id', 'ip', 'date'], 'IDX_LOG_CID');
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
        }

        if (!$schema->hasTable('fusio_page')) {
            $pageTable = $schema->createTable('fusio_page');
            $pageTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $pageTable->addColumn('status', 'integer', ['default' => Table\Page::STATUS_VISIBLE]);
            $pageTable->addColumn('title', 'string', ['length' => 255]);
            $pageTable->addColumn('slug', 'string', ['length' => 255]);
            $pageTable->addColumn('content', 'text');
            $pageTable->addColumn('date', 'datetime');
            $pageTable->setPrimaryKey(['id']);
            $pageTable->addUniqueIndex(['slug']);
        }

        if (!$schema->hasTable('fusio_plan')) {
            $planTable = $schema->createTable('fusio_plan');
            $planTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $planTable->addColumn('status', 'integer');
            $planTable->addColumn('name', 'string');
            $planTable->addColumn('description', 'string');
            $planTable->addColumn('price', 'decimal', ['precision' => 8, 'scale' => 2]);
            $planTable->addColumn('points', 'integer');
            $planTable->addColumn('period_type', 'integer', ['notnull' => false]);
            $planTable->addColumn('external_id', 'string', ['notnull' => false]);
            $planTable->setPrimaryKey(['id']);
            $planTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_plan_usage')) {
            $planUsageTable = $schema->createTable('fusio_plan_usage');
            $planUsageTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $planUsageTable->addColumn('route_id', 'integer');
            $planUsageTable->addColumn('user_id', 'integer');
            $planUsageTable->addColumn('app_id', 'integer');
            $planUsageTable->addColumn('points', 'integer');
            $planUsageTable->addColumn('insert_date', 'datetime');
            $planUsageTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_plan_scope')) {
            $planScopeTable = $schema->createTable('fusio_plan_scope');
            $planScopeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $planScopeTable->addColumn('plan_id', 'integer');
            $planScopeTable->addColumn('scope_id', 'integer');
            $planScopeTable->setPrimaryKey(['id']);
            $planScopeTable->addUniqueIndex(['plan_id', 'scope_id']);
        }

        if (!$schema->hasTable('fusio_provider')) {
            $providerTable = $schema->createTable('fusio_provider');
            $providerTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $providerTable->addColumn('type', 'string');
            $providerTable->addColumn('class', 'string');
            $providerTable->setPrimaryKey(['id']);
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
            $rateTable->addUniqueIndex(['name']);
            $rateTable->addIndex(['status'], 'IDX_RATE_S');
        }

        if (!$schema->hasTable('fusio_rate_allocation')) {
            $rateAllocationTable = $schema->createTable('fusio_rate_allocation');
            $rateAllocationTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $rateAllocationTable->addColumn('rate_id', 'integer');
            $rateAllocationTable->addColumn('route_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('user_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('plan_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('app_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('authenticated', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_role')) {
            $roleTable = $schema->createTable('fusio_role');
            $roleTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $roleTable->addColumn('category_id', 'integer');
            $roleTable->addColumn('status', 'integer');
            $roleTable->addColumn('name', 'string');
            $roleTable->setPrimaryKey(['id']);
            $roleTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_role_scope')) {
            $roleScopeTable = $schema->createTable('fusio_role_scope');
            $roleScopeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $roleScopeTable->addColumn('role_id', 'integer');
            $roleScopeTable->addColumn('scope_id', 'integer');
            $roleScopeTable->setPrimaryKey(['id']);
            $roleScopeTable->addUniqueIndex(['role_id', 'scope_id']);
        }

        if (!$schema->hasTable('fusio_routes')) {
            $routesTable = $schema->createTable('fusio_routes');
            $routesTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $routesTable->addColumn('category_id', 'integer', ['default' => 1]);
            $routesTable->addColumn('status', 'integer', ['default' => Table\Route::STATUS_ACTIVE]);
            $routesTable->addColumn('priority', 'integer', ['notnull' => false]);
            $routesTable->addColumn('methods', 'string', ['length' => 64]);
            $routesTable->addColumn('path', 'string', ['length' => 255]);
            $routesTable->addColumn('controller', 'string', ['length' => 255]);
            $routesTable->setPrimaryKey(['id']);
            $routesTable->addUniqueIndex(['path']);
            $routesTable->addIndex(['priority']);
            $routesTable->addIndex(['status'], 'IDX_ROUTE_S');
            $routesTable->addIndex(['category_id', 'status'], 'IDX_ROUTE_CS');
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
            $routesMethodTable->addColumn('operation_id', 'string', ['notnull' => false, 'length' => 255]);
            $routesMethodTable->addColumn('description', 'string', ['notnull' => false, 'length' => 500]);
            $routesMethodTable->addColumn('parameters', 'string', ['notnull' => false]);
            $routesMethodTable->addColumn('request', 'string', ['notnull' => false]);
            $routesMethodTable->addColumn('action', 'string', ['notnull' => false]);
            $routesMethodTable->addColumn('costs', 'integer', ['notnull' => false]);
            $routesMethodTable->setPrimaryKey(['id']);
            $routesMethodTable->addUniqueIndex(['route_id', 'method', 'version']);
        }

        if (!$schema->hasTable('fusio_routes_response')) {
            $routesResponseTable = $schema->createTable('fusio_routes_response');
            $routesResponseTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $routesResponseTable->addColumn('method_id', 'integer');
            $routesResponseTable->addColumn('code', 'smallint');
            $routesResponseTable->addColumn('response', 'string');
            $routesResponseTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_schema')) {
            $schemaTable = $schema->createTable('fusio_schema');
            $schemaTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $schemaTable->addColumn('category_id', 'integer', ['default' => 1]);
            $schemaTable->addColumn('status', 'integer', ['default' => Table\Schema::STATUS_ACTIVE]);
            $schemaTable->addColumn('name', 'string', ['length' => 255]);
            $schemaTable->addColumn('source', 'text');
            $schemaTable->addColumn('form', 'text', ['notnull' => false, 'default' => null]);
            $schemaTable->setPrimaryKey(['id']);
            $schemaTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_scope')) {
            $scopeTable = $schema->createTable('fusio_scope');
            $scopeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $scopeTable->addColumn('category_id', 'integer', ['default' => 1]);
            $scopeTable->addColumn('status', 'integer', ['default' => Table\Scope::STATUS_ACTIVE]);
            $scopeTable->addColumn('name', 'string', ['length' => 32]);
            $scopeTable->addColumn('description', 'string', ['length' => 255]);
            $scopeTable->setPrimaryKey(['id']);
            $scopeTable->addUniqueIndex(['name']);
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

        if (!$schema->hasTable('fusio_transaction')) {
            $transactionTable = $schema->createTable('fusio_transaction');
            $transactionTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $transactionTable->addColumn('user_id', 'integer');
            $transactionTable->addColumn('plan_id', 'integer');
            $transactionTable->addColumn('transaction_id', 'string');
            $transactionTable->addColumn('amount', 'integer');
            $transactionTable->addColumn('points', 'integer');
            $transactionTable->addColumn('period_start', 'datetime', ['notnull' => false]);
            $transactionTable->addColumn('period_end', 'datetime', ['notnull' => false]);
            $transactionTable->addColumn('insert_date', 'datetime');
            $transactionTable->setPrimaryKey(['id']);
            $transactionTable->addUniqueIndex(['transaction_id']);
        }

        if (!$schema->hasTable('fusio_user')) {
            $userTable = $schema->createTable('fusio_user');
            $userTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $userTable->addColumn('role_id', 'integer');
            $userTable->addColumn('plan_id', 'integer', ['notnull' => false]);
            $userTable->addColumn('provider', 'integer', ['default' => ProviderInterface::PROVIDER_SYSTEM]);
            $userTable->addColumn('status', 'integer');
            $userTable->addColumn('remote_id', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('external_id', 'string', ['notnull' => false]);
            $userTable->addColumn('name', 'string', ['length' => 64]);
            $userTable->addColumn('email', 'string', ['length' => 128, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('password', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('points', 'integer', ['notnull' => false]);
            $userTable->addColumn('token', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('date', 'datetime');
            $userTable->setPrimaryKey(['id']);
            $userTable->addUniqueIndex(['provider', 'remote_id']);
            $userTable->addUniqueIndex(['name']);
            $userTable->addUniqueIndex(['email']);
        }

        if (!$schema->hasTable('fusio_user_attribute')) {
            $userAttributeTable = $schema->createTable('fusio_user_attribute');
            $userAttributeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $userAttributeTable->addColumn('user_id', 'integer');
            $userAttributeTable->addColumn('name', 'string');
            $userAttributeTable->addColumn('value', 'string');
            $userAttributeTable->setPrimaryKey(['id']);
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

        if (isset($planScopeTable)) {
            $planScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'plan_scope_scope_id');
            $planScopeTable->addForeignKeyConstraint($schema->getTable('fusio_plan'), ['plan_id'], ['id'], [], 'plan_scope_user_id');
        }

        if (isset($rateAllocationTable)) {
            $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_rate'), ['rate_id'], ['id'], [], 'rate_allocation_rate_id');
            $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_routes'), ['route_id'], ['id'], [], 'rate_allocation_route_id');
            $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'rate_allocation_app_id');
        }

        if (isset($roleTable)) {
            $roleTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'role_category_id');
        }

        if (isset($roleScopeTable)) {
            $roleScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'role_scope_scope_id');
            $roleScopeTable->addForeignKeyConstraint($schema->getTable('fusio_role'), ['role_id'], ['id'], [], 'role_scope_role_id');
        }

        if (isset($routesMethodTable)) {
            $routesMethodTable->addForeignKeyConstraint($schema->getTable('fusio_routes'), ['route_id'], ['id'], [], 'routes_method_route_id');
        }

        if (isset($routesResponseTable)) {
            $routesResponseTable->addForeignKeyConstraint($schema->getTable('fusio_routes_method'), ['method_id'], ['id'], [], 'routes_response_method_id');
        }

        if (isset($scopeRoutesTable)) {
            $scopeRoutesTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'scope_routes_scope_id');
            $scopeRoutesTable->addForeignKeyConstraint($schema->getTable('fusio_routes'), ['route_id'], ['id'], [], 'scope_routes_route_id');
        }

        if (isset($userAttributeTable)) {
            $userAttributeTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'user_attribute_user_id');
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

    public function down(Schema $schema) : void
    {
        $schema->dropTable('fusio_action');
        $schema->dropTable('fusio_app');
        $schema->dropTable('fusio_app_code');
        $schema->dropTable('fusio_app_scope');
        $schema->dropTable('fusio_app_token');
        $schema->dropTable('fusio_audit');
        $schema->dropTable('fusio_config');
        $schema->dropTable('fusio_connection');
        $schema->dropTable('fusio_cronjob');
        $schema->dropTable('fusio_cronjob_error');
        $schema->dropTable('fusio_event');
        $schema->dropTable('fusio_event_response');
        $schema->dropTable('fusio_event_subscription');
        $schema->dropTable('fusio_event_trigger');
        $schema->dropTable('fusio_log');
        $schema->dropTable('fusio_log_error');
        $schema->dropTable('fusio_plan');
        $schema->dropTable('fusio_plan_usage');
        $schema->dropTable('fusio_provider');
        $schema->dropTable('fusio_rate');
        $schema->dropTable('fusio_rate_allocation');
        $schema->dropTable('fusio_role');
        $schema->dropTable('fusio_role_scope');
        $schema->dropTable('fusio_routes');
        $schema->dropTable('fusio_routes_method');
        $schema->dropTable('fusio_routes_response');
        $schema->dropTable('fusio_schema');
        $schema->dropTable('fusio_scope');
        $schema->dropTable('fusio_scope_routes');
        $schema->dropTable('fusio_user');
        $schema->dropTable('fusio_user_attribute');
        $schema->dropTable('fusio_user_grant');
        $schema->dropTable('fusio_user_scope');
    }

    /**
     * @see https://github.com/doctrine/migrations/issues/1104
     */
    public function isTransactional(): bool
    {
        return false;
    }

    public function postUp(Schema $schema): void
    {
        $inserts = NewInstallation::getData()->toArray();

        foreach ($inserts as $tableName => $rows) {
            if (!empty($rows)) {
                $count = $this->connection->fetchColumn('SELECT COUNT(*) AS cnt FROM ' . $tableName);
                if ($count > 0) {
                    continue;
                }

                foreach ($rows as $row) {
                    $this->connection->insert($tableName, $row);
                }
            }
        }
    }
}

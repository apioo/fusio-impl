<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;
use Fusio\Impl\Installation\NewInstallation;
use Fusio\Impl\Table;
use PSX\Api\Model\Passthru;
use PSX\Api\OperationInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230508210151 extends AbstractMigration
{
    private bool $legacy = false;

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
            $actionTable->addColumn('config', 'text', ['notnull' => false]);
            $actionTable->addColumn('metadata', 'text', ['notnull' => false]);
            $actionTable->addColumn('date', 'datetime');
            $actionTable->setPrimaryKey(['id']);
            $actionTable->addUniqueIndex(['name']);
        } else {
            $actionTable = $schema->getTable('fusio_action');
            $actionTable->dropColumn('engine');
            $this->legacy = true;
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
            $appTable->addColumn('metadata', 'text', ['notnull' => false]);
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
            $configTable->addColumn('description', 'string', ['length' => 512]);
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
            $connectionTable->addColumn('metadata', 'text', ['notnull' => false]);
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
            $cronjobTable->addColumn('metadata', 'text', ['notnull' => false]);
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
            $eventTable->addColumn('metadata', 'text', ['notnull' => false]);
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

        if (!$schema->hasTable('fusio_identity')) {
            $identityTable = $schema->createTable('fusio_identity');
            $identityTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $identityTable->addColumn('status', 'integer');
            $identityTable->addColumn('app_id', 'integer');
            $identityTable->addColumn('role_id', 'integer', ['notnull' => false]);
            $identityTable->addColumn('name', 'string', ['length' => 128]);
            $identityTable->addColumn('icon', 'string', ['length' => 64]);
            $identityTable->addColumn('class', 'string', ['length' => 255]);
            $identityTable->addColumn('config', 'text', ['notnull' => false]);
            $identityTable->addColumn('allow_create', 'boolean');
            $identityTable->addColumn('insert_date', 'datetime');
            $identityTable->setPrimaryKey(['id']);
            $identityTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_identity_request')) {
            $identityRequestTable = $schema->createTable('fusio_identity_request');
            $identityRequestTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $identityRequestTable->addColumn('identity_id', 'integer');
            $identityRequestTable->addColumn('state', 'string');
            $identityRequestTable->addColumn('redirect_uri', 'string', ['notnull' => false]);
            $identityRequestTable->addColumn('insert_date', 'datetime');
            $identityRequestTable->setPrimaryKey(['id']);
            $identityRequestTable->addUniqueIndex(['identity_id', 'state']);
        }

        if (!$schema->hasTable('fusio_log')) {
            $logTable = $schema->createTable('fusio_log');
            $logTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $logTable->addColumn('category_id', 'integer', ['default' => 1]);
            $logTable->addColumn('operation_id', 'integer', ['notnull' => false]);
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
        } else {
            $logTable = $schema->getTable('fusio_log');
            $logTable->addColumn('operation_id', 'integer', ['notnull' => false]);
            $logTable->dropColumn('route_id');
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

        if (!$schema->hasTable('fusio_operation')) {
            $operationTable = $schema->createTable('fusio_operation');
            $operationTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $operationTable->addColumn('category_id', 'integer', ['default' => 1]);
            $operationTable->addColumn('status', 'integer', ['default' => Table\Operation::STATUS_ACTIVE]);
            $operationTable->addColumn('active', 'integer', ['default' => 0]);
            $operationTable->addColumn('public', 'integer', ['default' => 0]);
            $operationTable->addColumn('stability', 'integer', ['default' => OperationInterface::STABILITY_EXPERIMENTAL]);
            $operationTable->addColumn('description', 'string', ['length' => 500, 'notnull' => false]);
            $operationTable->addColumn('http_method', 'string', ['length' => 16]);
            $operationTable->addColumn('http_path', 'string', ['length' => 255]);
            $operationTable->addColumn('http_code', 'integer');
            $operationTable->addColumn('name', 'string', ['length' => 255]);
            $operationTable->addColumn('parameters', 'text', ['notnull' => false]);
            $operationTable->addColumn('incoming', 'string', ['length' => 255, 'notnull' => false]);
            $operationTable->addColumn('outgoing', 'string', ['length' => 255]);
            $operationTable->addColumn('throws', 'text', ['notnull' => false]);
            $operationTable->addColumn('action', 'string', ['length' => 255]);
            $operationTable->addColumn('costs', 'integer', ['notnull' => false]);
            $operationTable->addColumn('metadata', 'text', ['notnull' => false]);
            $operationTable->setPrimaryKey(['id']);
            $operationTable->addUniqueIndex(['name']);
            $operationTable->addUniqueIndex(['http_method', 'http_path']);
            $operationTable->addIndex(['status'], 'IDX_OPERATION_S');
            $operationTable->addIndex(['category_id', 'status'], 'IDX_OPERATION_CS');
        }

        if (!$schema->hasTable('fusio_page')) {
            $pageTable = $schema->createTable('fusio_page');
            $pageTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $pageTable->addColumn('status', 'integer', ['default' => Table\Page::STATUS_VISIBLE]);
            $pageTable->addColumn('title', 'string', ['length' => 255]);
            $pageTable->addColumn('slug', 'string', ['length' => 255]);
            $pageTable->addColumn('content', 'text');
            $pageTable->addColumn('metadata', 'text', ['notnull' => false]);
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
            $planTable->addColumn('price', 'integer');
            $planTable->addColumn('points', 'integer');
            $planTable->addColumn('period_type', 'integer', ['notnull' => false]);
            $planTable->addColumn('external_id', 'string', ['notnull' => false]);
            $planTable->addColumn('metadata', 'text', ['notnull' => false]);
            $planTable->setPrimaryKey(['id']);
            $planTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_plan_usage')) {
            $planUsageTable = $schema->createTable('fusio_plan_usage');
            $planUsageTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $planUsageTable->addColumn('operation_id', 'integer');
            $planUsageTable->addColumn('user_id', 'integer');
            $planUsageTable->addColumn('app_id', 'integer');
            $planUsageTable->addColumn('points', 'integer');
            $planUsageTable->addColumn('insert_date', 'datetime');
            $planUsageTable->setPrimaryKey(['id']);
        } else {
            $planUsageTable = $schema->getTable('fusio_plan_usage');
            $planUsageTable->addColumn('operation_id', 'integer');
            $planUsageTable->dropColumn('route_id');
        }

        if (!$schema->hasTable('fusio_plan_scope')) {
            $planScopeTable = $schema->createTable('fusio_plan_scope');
            $planScopeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $planScopeTable->addColumn('plan_id', 'integer');
            $planScopeTable->addColumn('scope_id', 'integer');
            $planScopeTable->setPrimaryKey(['id']);
            $planScopeTable->addUniqueIndex(['plan_id', 'scope_id']);
        }

        if (!$schema->hasTable('fusio_rate')) {
            $rateTable = $schema->createTable('fusio_rate');
            $rateTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $rateTable->addColumn('status', 'integer');
            $rateTable->addColumn('priority', 'integer');
            $rateTable->addColumn('name', 'string', ['length' => 64]);
            $rateTable->addColumn('rate_limit', 'integer');
            $rateTable->addColumn('timespan', 'string');
            $rateTable->addColumn('metadata', 'text', ['notnull' => false]);
            $rateTable->setPrimaryKey(['id']);
            $rateTable->addUniqueIndex(['name']);
            $rateTable->addIndex(['status'], 'IDX_RATE_S');
        }

        if (!$schema->hasTable('fusio_rate_allocation')) {
            $rateAllocationTable = $schema->createTable('fusio_rate_allocation');
            $rateAllocationTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $rateAllocationTable->addColumn('rate_id', 'integer');
            $rateAllocationTable->addColumn('operation_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('user_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('plan_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('app_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->addColumn('authenticated', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->setPrimaryKey(['id']);
        } else {
            $rateAllocationTable = $schema->getTable('fusio_rate_allocation');
            $rateAllocationTable->addColumn('operation_id', 'integer', ['notnull' => false, 'default' => null]);
            $rateAllocationTable->dropColumn('route_id');
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

        if (!$schema->hasTable('fusio_schema')) {
            $schemaTable = $schema->createTable('fusio_schema');
            $schemaTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $schemaTable->addColumn('category_id', 'integer', ['default' => 1]);
            $schemaTable->addColumn('status', 'integer', ['default' => Table\Schema::STATUS_ACTIVE]);
            $schemaTable->addColumn('name', 'string', ['length' => 255]);
            $schemaTable->addColumn('source', 'text');
            $schemaTable->addColumn('form', 'text', ['notnull' => false, 'default' => null]);
            $schemaTable->addColumn('metadata', 'text', ['notnull' => false]);
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
            $scopeTable->addColumn('metadata', 'text', ['notnull' => false]);
            $scopeTable->setPrimaryKey(['id']);
            $scopeTable->addUniqueIndex(['name']);
        }

        if (!$schema->hasTable('fusio_scope_operation')) {
            $scopeOperationTable = $schema->createTable('fusio_scope_operation');
            $scopeOperationTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $scopeOperationTable->addColumn('scope_id', 'integer');
            $scopeOperationTable->addColumn('operation_id', 'integer');
            $scopeOperationTable->addColumn('allow', 'smallint');
            $scopeOperationTable->setPrimaryKey(['id']);
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
            $userTable->addColumn('identity_id', 'integer', ['notnull' => false]);
            $userTable->addColumn('status', 'integer');
            $userTable->addColumn('remote_id', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('external_id', 'string', ['notnull' => false]);
            $userTable->addColumn('name', 'string', ['length' => 64]);
            $userTable->addColumn('email', 'string', ['length' => 128, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('password', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('points', 'integer', ['notnull' => false]);
            $userTable->addColumn('token', 'string', ['length' => 255, 'notnull' => false, 'default' => null]);
            $userTable->addColumn('metadata', 'text', ['notnull' => false]);
            $userTable->addColumn('date', 'datetime');
            $userTable->setPrimaryKey(['id']);
            $userTable->addUniqueIndex(['identity_id', 'remote_id']);
            $userTable->addUniqueIndex(['name']);
            $userTable->addUniqueIndex(['email']);
        } else {
            $userTable = $schema->getTable('fusio_user');
            $userTable->addColumn('identity_id', 'integer', ['notnull' => false]);
            $userTable->dropColumn('provider');
            $this->legacy = true;
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

        if (isset($identityRequestTable)) {
            $identityRequestTable->addForeignKeyConstraint($schema->getTable('fusio_identity'), ['identity_id'], ['id'], [], 'identity_request_identity_id');
        }

        if (isset($planScopeTable)) {
            $planScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'plan_scope_scope_id');
            $planScopeTable->addForeignKeyConstraint($schema->getTable('fusio_plan'), ['plan_id'], ['id'], [], 'plan_scope_user_id');
        }

        $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_rate'), ['rate_id'], ['id'], [], 'rate_allocation_rate_id');
        $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_operation'), ['operation_id'], ['id'], [], 'rate_allocation_operation_id');
        $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'rate_allocation_app_id');

        if (isset($roleTable)) {
            $roleTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'role_category_id');
        }

        if (isset($roleScopeTable)) {
            $roleScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'role_scope_scope_id');
            $roleScopeTable->addForeignKeyConstraint($schema->getTable('fusio_role'), ['role_id'], ['id'], [], 'role_scope_role_id');
        }

        if (isset($scopeOperationTable)) {
            $scopeOperationTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'scope_operation_scope_id');
            $scopeOperationTable->addForeignKeyConstraint($schema->getTable('fusio_operation'), ['operation_id'], ['id'], [], 'scope_operation_operation_id');
        }

        $userTable->addForeignKeyConstraint($schema->getTable('fusio_identity'), ['identity_id'], ['id'], [], 'user_identity_id');

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
        $schema->dropTable('fusio_identity');
        $schema->dropTable('fusio_identity_request');
        $schema->dropTable('fusio_log');
        $schema->dropTable('fusio_log_error');
        $schema->dropTable('fusio_operation');
        $schema->dropTable('fusio_plan');
        $schema->dropTable('fusio_plan_usage');
        $schema->dropTable('fusio_rate');
        $schema->dropTable('fusio_rate_allocation');
        $schema->dropTable('fusio_role');
        $schema->dropTable('fusio_role_scope');
        $schema->dropTable('fusio_schema');
        $schema->dropTable('fusio_scope');
        $schema->dropTable('fusio_scope_operation');
        $schema->dropTable('fusio_user');
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
            if (empty($rows)) {
                continue;
            }

            $count = $this->connection->fetchOne('SELECT COUNT(*) AS cnt FROM ' . $tableName);
            if ($count > 0) {
                continue;
            }

            foreach ($rows as $row) {
                $this->connection->insert($tableName, $row);
            }
        }

        // upgrade legacy systems
        if ($this->legacy) {
            // remove legacy internal actions and schemas
            $this->connection->executeStatement('DELETE FROM fusio_action WHERE category_id IN (2, 3, 4, 5)');
            $this->connection->executeStatement('DELETE FROM fusio_schema WHERE category_id IN (2, 3, 4, 5)');

            // update schema class
            $this->connection->update('fusio_schema', ['source' => Passthru::class], ['name' => 'Passthru']);

            // sync data
            DataSyncronizer::sync($this->connection);
        }
    }
}

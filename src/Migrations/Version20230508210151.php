<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\NewInstallation;
use Fusio\Impl\Installation\Reference;
use Fusio\Impl\Table;
use PSX\Api\OperationInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230508210151 extends AbstractMigration
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
            $actionTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $actionTable->addColumn('category_id', 'integer', ['default' => 1]);
            $actionTable->addColumn('status', 'integer', ['default' => Table\Action::STATUS_ACTIVE]);
            $actionTable->addColumn('name', 'string', ['length' => 255]);
            $actionTable->addColumn('class', 'string', ['length' => 255]);
            $actionTable->addColumn('async', 'boolean', ['default' => false]);
            $actionTable->addColumn('config', 'text', ['notnull' => false]);
            $actionTable->addColumn('metadata', 'text', ['notnull' => false]);
            $actionTable->addColumn('date', 'datetime');
            $actionTable->setPrimaryKey(['id']);
            $actionTable->addUniqueIndex(['tenant_id', 'name']);
        }

        if (!$schema->hasTable('fusio_app')) {
            $appTable = $schema->createTable('fusio_app');
            $appTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $appTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
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
            $appTable->addUniqueIndex(['tenant_id', 'app_key']);
        }

        if (!$schema->hasTable('fusio_app_scope')) {
            $appScopeTable = $schema->createTable('fusio_app_scope');
            $appScopeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $appScopeTable->addColumn('app_id', 'integer');
            $appScopeTable->addColumn('scope_id', 'integer');
            $appScopeTable->setPrimaryKey(['id']);
            $appScopeTable->addUniqueIndex(['app_id', 'scope_id']);
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
            $auditTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $auditTable->addColumn('app_id', 'integer');
            $auditTable->addColumn('user_id', 'integer');
            $auditTable->addColumn('ref_id', 'integer', ['notnull' => false]);
            $auditTable->addColumn('event', 'string');
            $auditTable->addColumn('ip', 'string', ['length' => 40]);
            $auditTable->addColumn('message', 'string');
            $auditTable->addColumn('content', 'text', ['notnull' => false]);
            $auditTable->addColumn('date', 'datetime');
            $auditTable->setPrimaryKey(['id']);
            $auditTable->addIndex(['tenant_id']);
        }

        if (!$schema->hasTable('fusio_category')) {
            $categoryTable = $schema->createTable('fusio_category');
            $categoryTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $categoryTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $categoryTable->addColumn('status', 'integer');
            $categoryTable->addColumn('name', 'string', ['length' => 64]);
            $categoryTable->setPrimaryKey(['id']);
            $categoryTable->addUniqueIndex(['tenant_id', 'name']);
        }

        if (!$schema->hasTable('fusio_config')) {
            $configTable = $schema->createTable('fusio_config');
            $configTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $configTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $configTable->addColumn('type', 'integer', ['default' => 1]);
            $configTable->addColumn('name', 'string', ['length' => 64]);
            $configTable->addColumn('description', 'string', ['length' => 512]);
            $configTable->addColumn('value', 'string', ['length' => 512]);
            $configTable->setPrimaryKey(['id']);
            $configTable->addUniqueIndex(['tenant_id', 'name']);
        }

        if (!$schema->hasTable('fusio_connection')) {
            $connectionTable = $schema->createTable('fusio_connection');
            $connectionTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $connectionTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $connectionTable->addColumn('category_id', 'integer', ['default' => 1]);
            $connectionTable->addColumn('status', 'integer', ['default' => Table\Connection::STATUS_ACTIVE]);
            $connectionTable->addColumn('name', 'string', ['length' => 255]);
            $connectionTable->addColumn('class', 'string', ['length' => 255]);
            $connectionTable->addColumn('config', 'text', ['notnull' => false]);
            $connectionTable->addColumn('metadata', 'text', ['notnull' => false]);
            $connectionTable->setPrimaryKey(['id']);
            $connectionTable->addUniqueIndex(['tenant_id', 'name']);
        }

        if (!$schema->hasTable('fusio_cronjob')) {
            $cronjobTable = $schema->createTable('fusio_cronjob');
            $cronjobTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $cronjobTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $cronjobTable->addColumn('category_id', 'integer', ['default' => 1]);
            $cronjobTable->addColumn('status', 'integer', ['default' => Table\Cronjob::STATUS_ACTIVE]);
            $cronjobTable->addColumn('name', 'string', ['length' => 64]);
            $cronjobTable->addColumn('cron', 'string');
            $cronjobTable->addColumn('action', 'string', ['notnull' => false]);
            $cronjobTable->addColumn('execute_date', 'datetime', ['notnull' => false]);
            $cronjobTable->addColumn('exit_code', 'integer', ['notnull' => false]);
            $cronjobTable->addColumn('metadata', 'text', ['notnull' => false]);
            $cronjobTable->setPrimaryKey(['id']);
            $cronjobTable->addUniqueIndex(['tenant_id', 'name']);
        }

        if (!$schema->hasTable('fusio_cronjob_error')) {
            $cronjobErrorTable = $schema->createTable('fusio_cronjob_error');
            $cronjobErrorTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $cronjobErrorTable->addColumn('cronjob_id', 'integer');
            $cronjobErrorTable->addColumn('message', 'string', ['length' => 500]);
            $cronjobErrorTable->addColumn('trace', 'text');
            $cronjobErrorTable->addColumn('file', 'string', ['length' => 255]);
            $cronjobErrorTable->addColumn('line', 'integer');
            $cronjobErrorTable->addColumn('insert_date', 'datetime', ['notnull' => false]);
            $cronjobErrorTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_event')) {
            $eventTable = $schema->createTable('fusio_event');
            $eventTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $eventTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $eventTable->addColumn('category_id', 'integer', ['default' => 1]);
            $eventTable->addColumn('status', 'integer');
            $eventTable->addColumn('name', 'string', ['length' => 64]);
            $eventTable->addColumn('description', 'string', ['length' => 255]);
            $eventTable->addColumn('event_schema', 'string', ['notnull' => false]);
            $eventTable->addColumn('metadata', 'text', ['notnull' => false]);
            $eventTable->setPrimaryKey(['id']);
            $eventTable->addUniqueIndex(['tenant_id', 'name']);
        }

        if (!$schema->hasTable('fusio_firewall')) {
            $firewallTable = $schema->createTable('fusio_firewall');
            $firewallTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $firewallTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $firewallTable->addColumn('status', 'integer');
            $firewallTable->addColumn('name', 'string', ['length' => 64]);
            $firewallTable->addColumn('type', 'integer'); // allow/deny
            $firewallTable->addColumn('ip', 'string', ['length' => 39]);
            $firewallTable->addColumn('expire', 'datetime', ['notnull' => false]);
            $firewallTable->addColumn('metadata', 'text', ['notnull' => false]);
            $firewallTable->setPrimaryKey(['id']);
            $firewallTable->addUniqueIndex(['tenant_id', 'name']);
            $firewallTable->addIndex(['tenant_id', 'type', 'ip', 'expire']);
        }

        if (!$schema->hasTable('fusio_firewall_log')) {
            $firewallLogTable = $schema->createTable('fusio_firewall_log');
            $firewallLogTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $firewallLogTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $firewallLogTable->addColumn('ip', 'string', ['length' => 39]);
            $firewallLogTable->addColumn('response_code', 'integer');
            $firewallLogTable->addColumn('insert_date', 'datetime', ['notnull' => false]);
            $firewallLogTable->setPrimaryKey(['id']);
            $firewallLogTable->addUniqueIndex(['tenant_id', 'ip']);
        }

        if (!$schema->hasTable('fusio_form')) {
            $formTable = $schema->createTable('fusio_form');
            $formTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $formTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $formTable->addColumn('status', 'integer');
            $formTable->addColumn('name', 'string', ['length' => 64]);
            $formTable->addColumn('operation_id', 'integer');
            $formTable->addColumn('ui_schema', 'text');
            $formTable->addColumn('metadata', 'text', ['notnull' => false]);
            $formTable->setPrimaryKey(['id']);
            $formTable->addUniqueIndex(['tenant_id', 'name']);
        }

        if (!$schema->hasTable('fusio_identity')) {
            $identityTable = $schema->createTable('fusio_identity');
            $identityTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $identityTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
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
            $identityTable->addUniqueIndex(['tenant_id', 'name']);
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
            $logTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
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
            $logTable->addColumn('response_code', 'integer', ['notnull' => false, 'default' => null]);
            $logTable->addColumn('execution_time', 'integer', ['notnull' => false, 'default' => null]);
            $logTable->addColumn('date', 'datetime');
            $logTable->setPrimaryKey(['id']);
            $logTable->addIndex(['tenant_id', 'ip', 'date'], 'IDX_LOG_TID');
            $logTable->addIndex(['tenant_id', 'user_id', 'date'], 'IDX_LOG_TUD');
            $logTable->addIndex(['tenant_id', 'ip', 'response_code', 'date'], 'IDX_LOG_TIRD');
        }

        if (!$schema->hasTable('fusio_log_error')) {
            $logErrorTable = $schema->createTable('fusio_log_error');
            $logErrorTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $logErrorTable->addColumn('log_id', 'integer');
            $logErrorTable->addColumn('message', 'string', ['length' => 500]);
            $logErrorTable->addColumn('trace', 'text');
            $logErrorTable->addColumn('file', 'string', ['length' => 255]);
            $logErrorTable->addColumn('line', 'integer');
            $logErrorTable->addColumn('insert_date', 'datetime', ['notnull' => false]);
            $logErrorTable->setPrimaryKey(['id']);
        }

        if (!$schema->hasTable('fusio_mcp_session')) {
            $mcpSessionTable = $schema->createTable('fusio_mcp_session');
            $mcpSessionTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $mcpSessionTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $mcpSessionTable->addColumn('session_id', 'string', ['length' => 128]);
            $mcpSessionTable->addColumn('data', 'text');
            $mcpSessionTable->setPrimaryKey(['id']);
            $mcpSessionTable->addUniqueIndex(['tenant_id', 'session_id']);
        }

        if (!$schema->hasTable('fusio_operation')) {
            $operationTable = $schema->createTable('fusio_operation');
            $operationTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $operationTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
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
            $operationTable->addUniqueIndex(['tenant_id', 'name']);
            $operationTable->addUniqueIndex(['tenant_id', 'http_method', 'http_path']);
            $operationTable->addIndex(['status'], 'IDX_OPERATION_S');
            $operationTable->addIndex(['category_id', 'status'], 'IDX_OPERATION_CS');
        }

        if (!$schema->hasTable('fusio_page')) {
            $pageTable = $schema->createTable('fusio_page');
            $pageTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $pageTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $pageTable->addColumn('status', 'integer', ['default' => Table\Page::STATUS_VISIBLE]);
            $pageTable->addColumn('title', 'string', ['length' => 255]);
            $pageTable->addColumn('slug', 'string', ['length' => 255]);
            $pageTable->addColumn('content', 'text');
            $pageTable->addColumn('metadata', 'text', ['notnull' => false]);
            $pageTable->addColumn('date', 'datetime');
            $pageTable->setPrimaryKey(['id']);
            $pageTable->addUniqueIndex(['tenant_id', 'slug']);
        }

        if (!$schema->hasTable('fusio_plan')) {
            $planTable = $schema->createTable('fusio_plan');
            $planTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $planTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $planTable->addColumn('status', 'integer');
            $planTable->addColumn('name', 'string');
            $planTable->addColumn('description', 'string');
            $planTable->addColumn('price', 'integer');
            $planTable->addColumn('points', 'integer');
            $planTable->addColumn('period_type', 'integer', ['notnull' => false]);
            $planTable->addColumn('external_id', 'string', ['notnull' => false]);
            $planTable->addColumn('metadata', 'text', ['notnull' => false]);
            $planTable->setPrimaryKey(['id']);
            $planTable->addUniqueIndex(['tenant_id', 'name']);
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
            $rateTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $rateTable->addColumn('status', 'integer');
            $rateTable->addColumn('priority', 'integer');
            $rateTable->addColumn('name', 'string', ['length' => 64]);
            $rateTable->addColumn('rate_limit', 'integer');
            $rateTable->addColumn('timespan', 'string');
            $rateTable->addColumn('metadata', 'text', ['notnull' => false]);
            $rateTable->setPrimaryKey(['id']);
            $rateTable->addUniqueIndex(['tenant_id', 'name']);
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
        }

        if (!$schema->hasTable('fusio_role')) {
            $roleTable = $schema->createTable('fusio_role');
            $roleTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $roleTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $roleTable->addColumn('category_id', 'integer');
            $roleTable->addColumn('status', 'integer');
            $roleTable->addColumn('name', 'string');
            $roleTable->setPrimaryKey(['id']);
            $roleTable->addUniqueIndex(['tenant_id', 'name']);
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
            $schemaTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $schemaTable->addColumn('category_id', 'integer', ['default' => 1]);
            $schemaTable->addColumn('status', 'integer', ['default' => Table\Schema::STATUS_ACTIVE]);
            $schemaTable->addColumn('name', 'string', ['length' => 255]);
            $schemaTable->addColumn('source', 'text');
            $schemaTable->addColumn('form', 'text', ['notnull' => false, 'default' => null]);
            $schemaTable->addColumn('metadata', 'text', ['notnull' => false]);
            $schemaTable->setPrimaryKey(['id']);
            $schemaTable->addUniqueIndex(['tenant_id', 'name']);
        }

        if (!$schema->hasTable('fusio_scope')) {
            $scopeTable = $schema->createTable('fusio_scope');
            $scopeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $scopeTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $scopeTable->addColumn('category_id', 'integer', ['default' => 1]);
            $scopeTable->addColumn('status', 'integer', ['default' => Table\Scope::STATUS_ACTIVE]);
            $scopeTable->addColumn('name', 'string', ['length' => 32]);
            $scopeTable->addColumn('description', 'string', ['length' => 255]);
            $scopeTable->addColumn('metadata', 'text', ['notnull' => false]);
            $scopeTable->setPrimaryKey(['id']);
            $scopeTable->addUniqueIndex(['tenant_id', 'name']);
        }

        if (!$schema->hasTable('fusio_scope_operation')) {
            $scopeOperationTable = $schema->createTable('fusio_scope_operation');
            $scopeOperationTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $scopeOperationTable->addColumn('scope_id', 'integer');
            $scopeOperationTable->addColumn('operation_id', 'integer');
            $scopeOperationTable->addColumn('allow', 'smallint');
            $scopeOperationTable->setPrimaryKey(['id']);
            $scopeOperationTable->addUniqueIndex(['scope_id', 'operation_id'], 'IDX_SCOPE_OPERATION_SO');
        }

        if (!$schema->hasTable('fusio_test')) {
            $testTable = $schema->createTable('fusio_test');
            $testTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $testTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $testTable->addColumn('category_id', 'integer', ['default' => 1]);
            $testTable->addColumn('operation_id', 'integer', ['notnull' => false]);
            $testTable->addColumn('status', 'integer', ['default' => 1]);
            $testTable->addColumn('message', 'text', ['notnull' => false]);
            $testTable->addColumn('response', 'text', ['notnull' => false]);
            $testTable->addColumn('uri_fragments', 'string', ['length' => 512, 'notnull' => false]);
            $testTable->addColumn('parameters', 'string', ['length' => 512, 'notnull' => false]);
            $testTable->addColumn('headers', 'string', ['length' => 512, 'notnull' => false]);
            $testTable->addColumn('body', 'text', ['notnull' => false]);
            $testTable->setPrimaryKey(['id']);
            $testTable->addUniqueIndex(['operation_id']);

        }

        if (!$schema->hasTable('fusio_token')) {
            $tokenTable = $schema->createTable('fusio_token');
            $tokenTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $tokenTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $tokenTable->addColumn('category_id', 'integer', ['default' => 1]);
            $tokenTable->addColumn('app_id', 'integer', ['notnull' => false]);
            $tokenTable->addColumn('user_id', 'integer');
            $tokenTable->addColumn('status', 'integer', ['default' => 1]);
            $tokenTable->addColumn('name', 'string', ['length' => 255, 'notnull' => false]);
            $tokenTable->addColumn('token', 'string', ['length' => 512]);
            $tokenTable->addColumn('refresh', 'string', ['length' => 255, 'notnull' => false]);
            $tokenTable->addColumn('scope', 'string', ['length' => 1023]);
            $tokenTable->addColumn('ip', 'string', ['length' => 40]);
            $tokenTable->addColumn('expire', 'datetime', ['notnull' => false]);
            $tokenTable->addColumn('date', 'datetime');
            $tokenTable->setPrimaryKey(['id']);
            $tokenTable->addUniqueIndex(['tenant_id', 'status', 'token']);
            $tokenTable->addUniqueIndex(['tenant_id', 'refresh']);
        }

        if (!$schema->hasTable('fusio_transaction')) {
            $transactionTable = $schema->createTable('fusio_transaction');
            $transactionTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $transactionTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $transactionTable->addColumn('user_id', 'integer');
            $transactionTable->addColumn('plan_id', 'integer');
            $transactionTable->addColumn('transaction_id', 'string');
            $transactionTable->addColumn('amount', 'integer');
            $transactionTable->addColumn('points', 'integer');
            $transactionTable->addColumn('period_start', 'datetime', ['notnull' => false]);
            $transactionTable->addColumn('period_end', 'datetime', ['notnull' => false]);
            $transactionTable->addColumn('insert_date', 'datetime');
            $transactionTable->setPrimaryKey(['id']);
            $transactionTable->addUniqueIndex(['tenant_id', 'transaction_id']);
        }

        if (!$schema->hasTable('fusio_user')) {
            $userTable = $schema->createTable('fusio_user');
            $userTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $userTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
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
            $userTable->addUniqueIndex(['tenant_id', 'identity_id', 'remote_id']);
            $userTable->addUniqueIndex(['tenant_id', 'name']);
            $userTable->addUniqueIndex(['tenant_id', 'email']);
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

        if (!$schema->hasTable('fusio_webhook')) {
            $webhookTable = $schema->createTable('fusio_webhook');
            $webhookTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $webhookTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $webhookTable->addColumn('event_id', 'integer');
            $webhookTable->addColumn('user_id', 'integer');
            $webhookTable->addColumn('status', 'integer');
            $webhookTable->addColumn('name', 'string', ['length' => 32]);
            $webhookTable->addColumn('endpoint', 'string', ['length' => 255]);
            $webhookTable->setPrimaryKey(['id']);
            $webhookTable->addIndex(['tenant_id']);
        }

        if (!$schema->hasTable('fusio_webhook_response')) {
            $webhookResponseTable = $schema->createTable('fusio_webhook_response');
            $webhookResponseTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $webhookResponseTable->addColumn('webhook_id', 'integer');
            $webhookResponseTable->addColumn('status', 'integer');
            $webhookResponseTable->addColumn('attempts', 'integer');
            $webhookResponseTable->addColumn('code', 'integer', ['notnull' => false]);
            $webhookResponseTable->addColumn('body', 'text', ['notnull' => false]);
            $webhookResponseTable->addColumn('execute_date', 'datetime', ['notnull' => false]);
            $webhookResponseTable->addColumn('insert_date', 'datetime');
            $webhookResponseTable->setPrimaryKey(['id']);
        }

        if (isset($actionTable)) {
            $actionTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'action_category_id');
        }

        if (isset($appTable)) {
            $appTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'app_user_id');
        }

        if (isset($appScopeTable)) {
            $appScopeTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'app_scope_app_id');
            $appScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'app_scope_scope_id');
        }

        if (isset($cronjobTable)) {
            $cronjobTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'cronjob_category_id');
        }

        if (isset($eventTable)) {
            $eventTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'event_category_id');
        }

        if (isset($formTable)) {
            $formTable->addForeignKeyConstraint($schema->getTable('fusio_operation'), ['operation_id'], ['id'], [], 'form_operation_id');
        }

        if (isset($identityRequestTable)) {
            $identityRequestTable->addForeignKeyConstraint($schema->getTable('fusio_identity'), ['identity_id'], ['id'], [], 'identity_request_identity_id');
        }

        if (isset($operationTable)) {
            $operationTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'operation_category_id');
        }

        if (isset($tokenTable)) {
            $tokenTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'token_app_id');
            $tokenTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'token_user_id');
        }

        if (isset($planScopeTable)) {
            $planScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'plan_scope_scope_id');
            $planScopeTable->addForeignKeyConstraint($schema->getTable('fusio_plan'), ['plan_id'], ['id'], [], 'plan_scope_user_id');
        }

        if (isset($rateAllocationTable)) {
            $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_rate'), ['rate_id'], ['id'], [], 'rate_allocation_rate_id');
            $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_operation'), ['operation_id'], ['id'], [], 'rate_allocation_operation_id');
            $rateAllocationTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'rate_allocation_app_id');
        }

        if (isset($roleTable)) {
            $roleTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'role_category_id');
        }

        if (isset($roleScopeTable)) {
            $roleScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'role_scope_scope_id');
            $roleScopeTable->addForeignKeyConstraint($schema->getTable('fusio_role'), ['role_id'], ['id'], [], 'role_scope_role_id');
        }

        if (isset($schemaTable)) {
            $schemaTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'schema_category_id');
        }

        if (isset($scopeTable)) {
            $scopeTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'scope_category_id');
        }

        if (isset($scopeOperationTable)) {
            $scopeOperationTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'scope_operation_scope_id');
            $scopeOperationTable->addForeignKeyConstraint($schema->getTable('fusio_operation'), ['operation_id'], ['id'], [], 'scope_operation_operation_id');
        }

        if (isset($testTable)) {
            $testTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'test_category_id');
            $testTable->addForeignKeyConstraint($schema->getTable('fusio_operation'), ['operation_id'], ['id'], [], 'test_operation_id');
        }

        if (isset($tokenTable)) {
            $tokenTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'token_category_id');
        }

        if (isset($userTable)) {
            $userTable->addForeignKeyConstraint($schema->getTable('fusio_identity'), ['identity_id'], ['id'], [], 'user_identity_id');
        }

        if (isset($userGrantTable)) {
            $userGrantTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'user_grant_user_id');
            $userGrantTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'user_grant_app_id');
        }

        if (isset($userScopeTable)) {
            $userScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'user_scope_scope_id');
            $userScopeTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'user_scope_user_id');
        }

        if (isset($webhookTable)) {
            $webhookTable->addForeignKeyConstraint($schema->getTable('fusio_event'), ['event_id'], ['id'], [], 'webhook_event_id');
            $webhookTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'webhook_user_id');
        }

        if (isset($webhookResponseTable)) {
            $webhookResponseTable->addForeignKeyConstraint($schema->getTable('fusio_webhook'), ['webhook_id'], ['id'], [], 'webhook_response_webhook_id');
        }
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('fusio_action');
        $schema->dropTable('fusio_app');
        $schema->dropTable('fusio_app_code');
        $schema->dropTable('fusio_app_scope');
        $schema->dropTable('fusio_audit');
        $schema->dropTable('fusio_config');
        $schema->dropTable('fusio_connection');
        $schema->dropTable('fusio_cronjob');
        $schema->dropTable('fusio_cronjob_error');
        $schema->dropTable('fusio_event');
        $schema->dropTable('fusio_firewall');
        $schema->dropTable('fusio_form');
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
        $schema->dropTable('fusio_token');
        $schema->dropTable('fusio_user');
        $schema->dropTable('fusio_user_grant');
        $schema->dropTable('fusio_user_scope');
        $schema->dropTable('fusio_webhook');
        $schema->dropTable('fusio_webhook_response');
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
                foreach ($row as $key => $value) {
                    if ($value instanceof Reference) {
                        $row[$key] = $value->resolve($this->connection);
                    }
                }

                $this->connection->insert($tableName, $row);
            }
        }
    }
}

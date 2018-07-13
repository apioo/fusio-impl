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
        $actionTable = $schema->createTable('fusio_action');
        $actionTable->addColumn('id', 'integer', array('autoincrement' => true));
        $actionTable->addColumn('status', 'integer', array('default' => Table\Action::STATUS_ACTIVE));
        $actionTable->addColumn('name', 'string', array('length' => 255));
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
        $connectionTable->addColumn('name', 'string', array('length' => 255));
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

        $eventTable = $schema->createTable('fusio_event');
        $eventTable->addColumn('id', 'integer', array('autoincrement' => true));
        $eventTable->addColumn('status', 'integer');
        $eventTable->addColumn('name', 'string', array('length' => 64));
        $eventTable->addColumn('description', 'string', array('length' => 255));
        $eventTable->setPrimaryKey(array('id'));

        $eventResponseTable = $schema->createTable('fusio_event_response');
        $eventResponseTable->addColumn('id', 'integer', array('autoincrement' => true));
        $eventResponseTable->addColumn('triggerId', 'integer');
        $eventResponseTable->addColumn('subscriptionId', 'integer');
        $eventResponseTable->addColumn('status', 'integer');
        $eventResponseTable->addColumn('code', 'integer', array('notnull' => false));
        $eventResponseTable->addColumn('attempts', 'integer');
        $eventResponseTable->addColumn('executeDate', 'datetime', array('notnull' => false));
        $eventResponseTable->addColumn('insertDate', 'datetime');
        $eventResponseTable->setPrimaryKey(array('id'));

        $eventSubscriptionTable = $schema->createTable('fusio_event_subscription');
        $eventSubscriptionTable->addColumn('id', 'integer', array('autoincrement' => true));
        $eventSubscriptionTable->addColumn('eventId', 'integer');
        $eventSubscriptionTable->addColumn('userId', 'integer');
        $eventSubscriptionTable->addColumn('status', 'integer');
        $eventSubscriptionTable->addColumn('endpoint', 'string', array('length' => 255));
        $eventSubscriptionTable->setPrimaryKey(array('id'));

        $eventTriggerTable = $schema->createTable('fusio_event_trigger');
        $eventTriggerTable->addColumn('id', 'integer', array('autoincrement' => true));
        $eventTriggerTable->addColumn('eventId', 'integer');
        $eventTriggerTable->addColumn('status', 'integer');
        $eventTriggerTable->addColumn('payload', 'text');
        $eventTriggerTable->addColumn('insertDate', 'datetime');
        $eventTriggerTable->setPrimaryKey(array('id'));

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
        $routesTable->addColumn('priority', 'integer', array('notnull' => false));
        $routesTable->addColumn('methods', 'string', array('length' => 64));
        $routesTable->addColumn('path', 'string', array('length' => 255));
        $routesTable->addColumn('controller', 'string', array('length' => 255));
        $routesTable->setPrimaryKey(array('id'));
        $routesTable->addIndex(array('priority'));

        $routesMethodTable = $schema->createTable('fusio_routes_method');
        $routesMethodTable->addColumn('id', 'integer', array('autoincrement' => true));
        $routesMethodTable->addColumn('routeId', 'integer');
        $routesMethodTable->addColumn('method', 'string', array('length' => 8));
        $routesMethodTable->addColumn('version', 'integer');
        $routesMethodTable->addColumn('status', 'integer');
        $routesMethodTable->addColumn('active', 'integer', array('default' => 0));
        $routesMethodTable->addColumn('public', 'integer', array('default' => 0));
        $routesMethodTable->addColumn('description', 'string', array('notnull' => false, 'length' => 500));
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
        $schemaTable->addColumn('name', 'string', array('length' => 255));
        $schemaTable->addColumn('source', 'text');
        $schemaTable->addColumn('cache', 'blob');
        $schemaTable->addColumn('form', 'text', array('notnull' => false, 'default' => null));
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

        $eventResponseTable->addForeignKeyConstraint($eventTriggerTable, array('triggerId'), array('id'), array(), 'eventResponseTriggerId');
        $eventResponseTable->addForeignKeyConstraint($eventSubscriptionTable, array('subscriptionId'), array('id'), array(), 'eventResponseSubscriptionId');

        $eventSubscriptionTable->addForeignKeyConstraint($eventTable, array('eventId'), array('id'), array(), 'eventSubscriptionEventId');
        $eventSubscriptionTable->addForeignKeyConstraint($userTable, array('userId'), array('id'), array(), 'eventSubscriptionUserId');

        $eventTriggerTable->addForeignKeyConstraint($eventTable, array('eventId'), array('id'), array(), 'eventTriggerEventId');

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
        $schema->dropTable('fusio_meta');
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

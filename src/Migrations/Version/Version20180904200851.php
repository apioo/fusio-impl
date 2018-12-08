<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Fusio\Impl\Backend;
use Fusio\Impl\Consumer;
use Fusio\Impl\Migrations\MigrationUtil;
use Fusio\Impl\Migrations\NewInstallation;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180904200851 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $planTable = $schema->createTable('fusio_plan');
        $planTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $planTable->addColumn('status', 'integer');
        $planTable->addColumn('name', 'string');
        $planTable->addColumn('description', 'string');
        $planTable->addColumn('price', 'decimal', ['precision' => 8, 'scale' => 2]);
        $planTable->addColumn('points', 'integer');
        $planTable->setPrimaryKey(['id']);

        $planUsageTable = $schema->createTable('fusio_plan_usage');
        $planUsageTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $planUsageTable->addColumn('route_id', 'integer');
        $planUsageTable->addColumn('user_id', 'integer');
        $planUsageTable->addColumn('app_id', 'integer');
        $planUsageTable->addColumn('points', 'integer');
        $planUsageTable->addColumn('insert_date', 'datetime');
        $planUsageTable->setPrimaryKey(['id']);
        $planUsageTable->addOption('engine', 'MyISAM');

        $planTransactionTable = $schema->createTable('fusio_transaction');
        $planTransactionTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $planTransactionTable->addColumn('plan_id', 'integer');
        $planTransactionTable->addColumn('user_id', 'integer');
        $planTransactionTable->addColumn('app_id', 'integer');
        $planTransactionTable->addColumn('status', 'integer');
        $planTransactionTable->addColumn('provider', 'string');
        $planTransactionTable->addColumn('transaction_id', 'string');
        $planTransactionTable->addColumn('remote_id', 'string', ['notnull' => false]);
        $planTransactionTable->addColumn('amount', 'decimal', ['precision' => 8, 'scale' => 2]);
        $planTransactionTable->addColumn('return_url', 'string');
        $planTransactionTable->addColumn('update_date', 'datetime', ['notnull' => false]);
        $planTransactionTable->addColumn('insert_date', 'datetime');
        $planTransactionTable->setPrimaryKey(['id']);
        $planTransactionTable->addUniqueIndex(['transaction_id']);

        $userTable = $schema->getTable('fusio_user');
        if (!$userTable->hasColumn('points')) {
            $userTable->addColumn('points', 'integer', ['notnull' => false]);
        }

        $routesTable = $schema->getTable('fusio_routes_method');
        if (!$routesTable->hasColumn('costs')) {
            $routesTable->addColumn('costs', 'integer', ['notnull' => false]);
        }

        $planTransactionTable->addForeignKeyConstraint($schema->getTable('fusio_plan'), ['plan_id'], ['id'], [], 'plan_transaction_plan_id');
        $planTransactionTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'plan_transaction_user_id');
        $planTransactionTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'plan_transaction_app_id');

        // remove action and connection class table which is now available
        // through the provider file
        if ($schema->hasTable('fusio_action_class')) {
            $schema->dropTable('fusio_action_class');
        }

        if ($schema->hasTable('fusio_connection_class')) {
            $schema->dropTable('fusio_connection_class');
        }

        // sync
        MigrationUtil::sync($this->connection, function($sql, $params){
            $this->addSql($sql, $params);
        });
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('fusio_transaction');
        $schema->dropTable('fusio_plan_usage');
        $schema->dropTable('fusio_plan');
    }
}

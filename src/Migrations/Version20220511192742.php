<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220511192742 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $rateAllocationTable = $schema->getTable('fusio_rate_allocation');
        if (!$rateAllocationTable->hasColumn('plan_id')) {
            $rateAllocationTable->addColumn('user_id', 'integer', ['notnull' => false]);
            $rateAllocationTable->addColumn('plan_id', 'integer', ['notnull' => false]);
            $rateAllocationTable->dropColumn('parameters');
        }

        $planTable = $schema->getTable('fusio_plan');
        if (!$planTable->hasColumn('external_id')) {
            $planTable->addColumn('external_id', 'string', ['notnull' => false]);
        }

        $transactionTable = $schema->getTable('fusio_transaction');
        if (!$transactionTable->hasColumn('user_id')) {
            $transactionTable->addColumn('user_id', 'integer');
            $transactionTable->addColumn('plan_id', 'integer');
            $transactionTable->addColumn('points', 'integer');
            $transactionTable->changeColumn('amount', ['type' => Type::getType('integer')]);
            $transactionTable->dropColumn('invoice_id');
            $transactionTable->dropColumn('status');
            $transactionTable->dropColumn('remote_id');
            $transactionTable->dropColumn('provider');
            $transactionTable->dropColumn('return_url');
            $transactionTable->dropColumn('update_date');
        }

        $userTable = $schema->getTable('fusio_user');
        if (!$userTable->hasColumn('plan_id')) {
            $userTable->addColumn('plan_id', 'integer', ['notnull' => false]);
            $userTable->addColumn('external_id', 'string', ['notnull' => false]);
        }

        if (!$schema->hasTable('fusio_plan_scope')) {
            $planScopeTable = $schema->createTable('fusio_plan_scope');
            $planScopeTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $planScopeTable->addColumn('plan_id', 'integer');
            $planScopeTable->addColumn('scope_id', 'integer');
            $planScopeTable->setPrimaryKey(['id']);
            $planScopeTable->addUniqueIndex(['plan_id', 'scope_id']);

            $planScopeTable->addForeignKeyConstraint($schema->getTable('fusio_scope'), ['scope_id'], ['id'], [], 'plan_scope_scope_id');
            $planScopeTable->addForeignKeyConstraint($schema->getTable('fusio_plan'), ['plan_id'], ['id'], [], 'plan_scope_user_id');
        }

        if ($schema->hasTable('fusio_plan_contract')) {
            $schema->dropTable('fusio_plan_contract');
        }

        if ($schema->hasTable('fusio_plan_invoice')) {
            $schema->dropTable('fusio_plan_invoice');
        }
    }

    public function down(Schema $schema) : void
    {
    }

    public function postUp(Schema $schema): void
    {
        DataSyncronizer::sync($this->connection);

        $this->connection->delete('fusio_cronjob', ['name' => 'Billing_Run']);
    }
}

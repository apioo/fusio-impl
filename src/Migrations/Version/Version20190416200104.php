<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Fusio\Impl\Migrations\MigrationUtil;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190416200104 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $planTable = $schema->getTable('fusio_plan');
        if (!$planTable->hasColumn('period')) {
            $planTable->addColumn('period', 'integer', ['notnull' => false]);
        }

        $transactionTable = $schema->getTable('fusio_transaction');
        if (!$transactionTable->hasColumn('invoice_id')) {
            $transactionTable->addColumn('invoice_id', 'integer');

            $fks = [
                'plan_transaction_plan_id',
                'plan_transaction_user_id',
                'plan_transaction_app_id',
            ];

            foreach ($fks as $fk) {
                if ($transactionTable->hasForeignKey($fk)) {
                    $transactionTable->removeForeignKey($fk);
                }
            }

            $transactionTable->dropColumn('plan_id');
            $transactionTable->dropColumn('user_id');
            $transactionTable->dropColumn('app_id');
        }

        $userAttributeTable = $schema->createTable('fusio_user_attribute');
        $userAttributeTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $userAttributeTable->addColumn('user_id', 'integer');
        $userAttributeTable->addColumn('name', 'string');
        $userAttributeTable->addColumn('value', 'string');
        $userAttributeTable->setPrimaryKey(['id']);

        $planContractTable = $schema->createTable('fusio_plan_contract');
        $planContractTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $planContractTable->addColumn('user_id', 'integer');
        $planContractTable->addColumn('plan_id', 'integer');
        $planContractTable->addColumn('status', 'integer');
        $planContractTable->addColumn('amount', 'decimal', ['precision' => 8, 'scale' => 2]);
        $planContractTable->addColumn('points', 'integer');
        $planContractTable->addColumn('period', 'integer', ['notnull' => false]);
        $planContractTable->addColumn('insert_date', 'datetime');
        $planContractTable->setPrimaryKey(['id']);

        $planInvoiceTable = $schema->createTable('fusio_plan_invoice');
        $planInvoiceTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $planInvoiceTable->addColumn('contract_id', 'integer');
        $planInvoiceTable->addColumn('prev_id', 'integer', ['notnull' => false]);
        $planInvoiceTable->addColumn('status', 'integer');
        $planInvoiceTable->addColumn('amount', 'decimal', ['precision' => 8, 'scale' => 2]);
        $planInvoiceTable->addColumn('points', 'integer');
        $planInvoiceTable->addColumn('from_date', 'date');
        $planInvoiceTable->addColumn('to_date', 'date');
        $planInvoiceTable->addColumn('pay_date', 'datetime', ['notnull' => false]);
        $planInvoiceTable->addColumn('insert_date', 'datetime');
        $planInvoiceTable->setPrimaryKey(['id']);

        $userAttributeTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'user_attribute_user_id');

        $planContractTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'plan_contract_user_id');
        $planContractTable->addForeignKeyConstraint($schema->getTable('fusio_plan'), ['plan_id'], ['id'], [], 'plan_contract_plan_id');

        $planInvoiceTable->addForeignKeyConstraint($schema->getTable('fusio_plan_contract'), ['contract_id'], ['id'], [], 'plan_invoice_contract_id');
        $planInvoiceTable->addForeignKeyConstraint($schema->getTable('fusio_plan_invoice'), ['prev_id'], ['id'], [], 'plan_invoice_prev_id');

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
        $schema->dropTable('fusio_user_attribute');
        $schema->dropTable('fusio_plan_contract');
        $schema->dropTable('fusio_plan_invoice');
    }
}

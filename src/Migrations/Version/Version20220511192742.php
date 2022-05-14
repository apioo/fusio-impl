<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Table;

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
        $rateTable = $schema->getTable('fusio_rate');
        if (!$rateTable->hasColumn('plan_id')) {
            $rateTable->addColumn('plan_id', 'integer', ['notnull' => false]);
        }

        $planTable = $schema->getTable('fusio_plan');
        if (!$planTable->hasColumn('external_id')) {
            $planTable->addColumn('external_id', 'string', ['notnull' => false]);
        }

        $transactionTable = $schema->getTable('fusio_transaction');
        if (!$transactionTable->hasColumn('user_id')) {
            $transactionTable->addColumn('user_id', 'integer');
            $transactionTable->addColumn('plan_id', 'integer');
            $transactionTable->dropColumn('invoice_id');
            $transactionTable->dropColumn('status');
            $transactionTable->dropColumn('remote_id');
            $transactionTable->dropColumn('return_url');
            $transactionTable->dropColumn('update_date');
        }

        $userTable = $schema->getTable('fusio_user');
        if (!$userTable->hasColumn('plan_id')) {
            $userTable->addColumn('plan_id', 'integer', ['notnull' => false]);
            $userTable->addColumn('external_id', 'string', ['notnull' => false]);
        }

        // add new config
        $configs = [
            [Table\Config::FORM_STRING, 'payment_stripe_secret', 'The stripe webhook secret which is needed to verify a webhook request', ''],
            [Table\Config::FORM_STRING, 'payment_currency', 'The three-character ISO-4217 currency code which is used to process payments', ''],
        ];

        foreach ($configs as $row) {
            $id = $this->connection->fetchOne('SELECT id FROM fusio_config WHERE name = ?', [$row[1]]);
            if (empty($id)) {
                $this->addSql('INSERT INTO fusio_config (type, name, description, value) VALUES (?, ?, ?, ?)', $row);
            }
        }
    }

    public function down(Schema $schema) : void
    {
    }
}

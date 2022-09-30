<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220930192001 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $transactionTable = $schema->getTable('fusio_transaction');
        if (!$transactionTable->hasColumn('period_start')) {
            $transactionTable->addColumn('period_start', 'datetime', ['notnull' => false]);
            $transactionTable->addColumn('period_end', 'datetime', ['notnull' => false]);
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}

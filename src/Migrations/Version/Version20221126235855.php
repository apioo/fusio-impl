<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221126235855 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        if (!$schema->hasTable('fusio_system_usage')) {
            $systemUsageTable = $schema->createTable('fusio_system_usage');
            $systemUsageTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $systemUsageTable->addColumn('cpu_usage', 'integer');
            $systemUsageTable->addColumn('memory_usage', 'integer');
            $systemUsageTable->addColumn('insert_date', 'datetime');
            $systemUsageTable->setPrimaryKey(['id']);
        }
    }

    public function down(Schema $schema) : void
    {
    }
}

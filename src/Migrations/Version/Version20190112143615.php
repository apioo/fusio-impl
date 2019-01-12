<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190112143615 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $providerTable = $schema->createTable('fusio_provider');
        $providerTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $providerTable->addColumn('type', 'string');
        $providerTable->addColumn('class', 'string');
        $providerTable->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->dropTable('fusio_provider');
    }
}

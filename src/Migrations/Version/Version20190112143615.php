<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190112143615 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
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
    public function down(Schema $schema): void
    {
        $schema->dropTable('fusio_provider');
    }
}

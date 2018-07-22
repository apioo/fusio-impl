<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 * Inserts the test data if in test mode
 */
class Version20180713132458 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->skipIf(!defined('FUSIO_IN_TEST'), 'Skipped test data');

        $appTable = $schema->createTable('app_news');
        $appTable->addColumn('id', 'integer', ['autoincrement' => true]);
        $appTable->addColumn('title', 'string', ['length' => 64]);
        $appTable->addColumn('content', 'string', ['length' => 255]);
        $appTable->addColumn('date', 'datetime');
        $appTable->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->skipIf(!defined('FUSIO_IN_TEST'), 'Skipped test data');

        $schema->dropTable('app_news');
    }
}

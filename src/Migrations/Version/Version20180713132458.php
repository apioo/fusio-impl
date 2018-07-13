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

        $actionTable = $schema->createTable('app_news');
        $actionTable->addColumn('id', 'integer', array('autoincrement' => true));
        $actionTable->addColumn('title', 'string', array('length' => 64));
        $actionTable->addColumn('content', 'string', array('length' => 255));
        $actionTable->addColumn('date', 'datetime');
        $actionTable->setPrimaryKey(array('id'));
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

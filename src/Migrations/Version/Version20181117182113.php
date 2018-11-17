<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181117182113 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $appTokenTable = $schema->getTable('fusio_app_token');

        // increase token size
        $appTokenTable->changeColumn('token', ['length' => 512]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
    }
}

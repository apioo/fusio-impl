<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181117182113 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $appTokenTable = $schema->getTable('fusio_app_token');

        if ($appTokenTable->hasIndex('token')) {
            // increase token size and remove index
            $appTokenTable->changeColumn('token', ['length' => 512]);
            $appTokenTable->dropIndex('token');
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
    }
}

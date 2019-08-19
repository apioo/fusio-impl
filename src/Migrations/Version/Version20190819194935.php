<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Fusio\Impl\Migrations\MigrationUtil;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190819194935 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
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
    }
}

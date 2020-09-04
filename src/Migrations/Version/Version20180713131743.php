<?php

namespace Fusio\Impl\Migrations\Version;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Fusio\Adapter;
use Fusio\Impl\Backend;
use Fusio\Impl\Consumer;
use Fusio\Impl\Export;
use Fusio\Impl\Migrations\NewInstallation;
use Fusio\Impl\Migrations\MigrationUtil;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180713131743 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        $inserts = NewInstallation::getData();

        foreach ($inserts as $tableName => $rows) {
            if (!empty($rows)) {
                $count = $this->connection->fetchColumn('SELECT COUNT(*) AS cnt FROM ' . $tableName);
                if ($count > 0) {
                    continue;
                }

                foreach ($rows as $row) {
                    MigrationUtil::insertRow($tableName, $row, function($sql, $params){
                        $this->addSql($sql, $params);
                    });
                }
            }
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        $tableNames = $schema->getTableNames();

        $inserts = array_keys(NewInstallation::getData());
        $inserts = array_reverse($inserts);

        foreach ($inserts as $tableName) {
            // check whether table exists
            $found = false;
            foreach ($tableNames as $name) {
                if (strpos($name, $tableName) !== false) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $this->addSql('DELETE FROM ' . $tableName . ' WHERE 1=1');
            }
        }
    }
}

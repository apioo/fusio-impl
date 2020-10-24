<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Migrations\MigrationUtil;
use Fusio\Impl\Migrations\NewInstallation;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200905191429 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Insert new installation data';
    }

    public function up(Schema $schema) : void
    {
        $inserts = NewInstallation::getData()->toArray();

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

    public function down(Schema $schema) : void
    {
        $tableNames = $schema->getTableNames();

        $inserts = array_keys(NewInstallation::getData()->toArray());
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

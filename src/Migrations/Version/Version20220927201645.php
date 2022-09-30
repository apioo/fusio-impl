<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations\Version;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Migrations\NewInstallation;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220927201645 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addMetaDataColumn($schema, 'fusio_app');
        $this->addMetaDataColumn($schema, 'fusio_user');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    public function postUp(Schema $schema): void
    {
        $users = $this->connection->fetchAllAssociative('SELECT id FROM fusio_user');
        foreach ($users as $user) {
            $result = $this->connection->fetchAllAssociative('SELECT name, value FROM fusio_user_attribute WHERE user_id = :user_id', [
                'user_id' => $user['id']
            ]);

            $metadata = [];
            foreach ($result as $row) {
                $metadata[$row['name']] = $row['value'];
            }

            if (!empty($metadata)) {
                $this->connection->update('fusio_user', [
                    'metadata' => json_encode($metadata)
                ], [
                    'id' => $user['id']
                ]);
            }
        }
    }

    private function addMetaDataColumn(Schema $schema, string $tableName)
    {
        $table = $schema->getTable($tableName);
        if (!$table->hasColumn('metadata')) {
            $table->addColumn('metadata', 'text', ['notnull' => false]);
        }
    }
}

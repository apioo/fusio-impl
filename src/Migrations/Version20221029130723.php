<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221029130723 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    public function postUp(Schema $schema): void
    {
        $result = $this->connection->fetchAllAssociative('SELECT * FROM fusio_schema');
        foreach ($result as $row) {
            $source = $row['source'] ?? '';
            if (!str_starts_with($source, 'Fusio\\Model\\')) {
                continue;
            }

            if (!str_contains($source, '_')) {
                continue;
            }

            $source = str_replace('_', '', $source);
            if (!class_exists($source)) {
                continue;
            }

            $this->connection->update('fusio_schema', [
                'source' => $source
            ], [
                'id' => $row['id']
            ]);
        }
    }
}

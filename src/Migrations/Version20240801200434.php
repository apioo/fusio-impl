<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240801200434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $connectionTable = $schema->getTable('fusio_connection');
        if (!$connectionTable->hasColumn('category_id')) {
            $connectionTable->addColumn('category_id', 'integer', ['default' => 1]);
        }
    }

    public function down(Schema $schema): void
    {
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function postUp(Schema $schema): void
    {
        DataSyncronizer::sync($this->connection);

        // update test incoming
        $this->connection->update('fusio_operation', [
            'incoming' => 'php+class://Fusio.Model.Backend.Test'
        ], [
            'http_path' => '/backend/test/$test_id<[0-9]+>',
            'http_method' => 'PUT',
            'incoming' => 'php+class://Fusio.Model.Backend.TestConfig'
        ]);
    }
}

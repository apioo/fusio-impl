<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251130202609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('fusio_bundle')) {
            $bundleTable = $schema->createTable('fusio_bundle');
            $bundleTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $bundleTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $bundleTable->addColumn('category_id', 'integer', ['default' => 1]);
            $bundleTable->addColumn('status', 'integer');
            $bundleTable->addColumn('name', 'string', ['length' => 255]);
            $bundleTable->addColumn('config', 'text');
            $bundleTable->addColumn('metadata', 'text', ['notnull' => false]);
            $bundleTable->setPrimaryKey(['id']);
            $bundleTable->addUniqueIndex(['tenant_id', 'name']);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function postUp(Schema $schema): void
    {
        DataSyncronizer::sync($this->connection);
    }
}

<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251008163109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('fusio_trigger')) {
            $triggerTable = $schema->createTable('fusio_trigger');
            $triggerTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $triggerTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $triggerTable->addColumn('category_id', 'integer', ['default' => 1]);
            $triggerTable->addColumn('status', 'integer');
            $triggerTable->addColumn('name', 'string', ['length' => 255]);
            $triggerTable->addColumn('event', 'string', ['length' => 64]);
            $triggerTable->addColumn('action', 'string', ['length' => 255]);
            $triggerTable->addColumn('metadata', 'text', ['notnull' => false]);
            $triggerTable->setPrimaryKey(['id']);
            $triggerTable->addUniqueIndex(['tenant_id', 'name']);
            $triggerTable->addIndex(['tenant_id', 'event']);
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
    }
}

<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250323184048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('fusio_form')) {
            $formTable = $schema->createTable('fusio_form');
            $formTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $formTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $formTable->addColumn('status', 'integer');
            $formTable->addColumn('name', 'string', ['length' => 64]);
            $formTable->addColumn('operation_id', 'integer');
            $formTable->addColumn('ui_schema', 'text');
            $formTable->addColumn('metadata', 'text', ['notnull' => false]);
            $formTable->setPrimaryKey(['id']);
            $formTable->addUniqueIndex(['tenant_id', 'name']);

            $formTable->addForeignKeyConstraint($schema->getTable('fusio_operation'), ['operation_id'], ['id'], [], 'form_operation_id');
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

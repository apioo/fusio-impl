<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210222824 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('fusio_taxonomy')) {
            $taxonomyTable = $schema->createTable('fusio_taxonomy');
            $taxonomyTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $taxonomyTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $taxonomyTable->addColumn('parent_id', 'integer', ['notnull' => false]);
            $taxonomyTable->addColumn('status', 'integer', ['default' => 1]);
            $taxonomyTable->addColumn('name', 'string');
            $taxonomyTable->addColumn('insert_date', 'datetime');
            $taxonomyTable->setPrimaryKey(['id']);
            $taxonomyTable->addUniqueIndex(['tenant_id', 'name']);

            $taxonomyTable->addForeignKeyConstraint($schema->getTable('fusio_taxonomy'), ['parent_id'], ['id'], [], 'taxonomy_parent_id');
        }

        $taxonomyTargets = [
            'operation',
            'action',
            'event',
            'cronjob',
            'trigger',
        ];

        foreach ($taxonomyTargets as $taxonomyTarget) {
            $table = $schema->getTable('fusio_' . $taxonomyTarget);
            if (!$table->hasColumn('taxonomy_id')) {
                $table->addColumn('taxonomy_id', 'integer', ['notnull' => false, 'default' => null]);
                $table->addForeignKeyConstraint($schema->getTable('fusio_taxonomy'), ['taxonomy_id'], ['id'], [], $taxonomyTarget . '_taxonomy_id');
            }
        }

        // add missing category foreign key
        $triggerTable = $schema->getTable('fusio_trigger');
        if (!$triggerTable->hasUniqueConstraint('trigger_category_id')) {
            $triggerTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'trigger_category_id');
        }
    }

    public function down(Schema $schema): void
    {
    }

    public function isTransactional(): bool
    {
        return false;
    }
}

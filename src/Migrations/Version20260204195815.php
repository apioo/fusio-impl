<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204195815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('fusio_action_commit')) {
            $actionCommitTable = $schema->createTable('fusio_action_commit');
            $actionCommitTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $actionCommitTable->addColumn('action_id', 'integer');
            $actionCommitTable->addColumn('user_id', 'integer');
            $actionCommitTable->addColumn('prev_hash', 'string', ['length' => 40]);
            $actionCommitTable->addColumn('commit_hash', 'string', ['length' => 40]);
            $actionCommitTable->addColumn('config', 'text');
            $actionCommitTable->addColumn('insert_date', 'datetime');
            $actionCommitTable->setPrimaryKey(['id']);
            $actionCommitTable->addUniqueIndex(['commit_hash']);

            $actionCommitTable->addForeignKeyConstraint($schema->getTable('fusio_action'), ['action_id'], ['id'], [], 'action_commit_action_id');
            $actionCommitTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'action_commit_user_id');
        }

        if (!$schema->hasTable('fusio_schema_commit')) {
            $schemaCommitTable = $schema->createTable('fusio_schema_commit');
            $schemaCommitTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $schemaCommitTable->addColumn('schema_id', 'integer');
            $schemaCommitTable->addColumn('user_id', 'integer');
            $schemaCommitTable->addColumn('prev_hash', 'string', ['length' => 40]);
            $schemaCommitTable->addColumn('commit_hash', 'string', ['length' => 40]);
            $schemaCommitTable->addColumn('source', 'text');
            $schemaCommitTable->addColumn('insert_date', 'datetime');
            $schemaCommitTable->setPrimaryKey(['id']);
            $schemaCommitTable->addUniqueIndex(['commit_hash']);

            $schemaCommitTable->addForeignKeyConstraint($schema->getTable('fusio_schema'), ['schema_id'], ['id'], [], 'schema_commit_action_id');
            $schemaCommitTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'schema_commit_user_id');
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

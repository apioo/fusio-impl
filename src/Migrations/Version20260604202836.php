<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604202836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $agentTable = $schema->getTable('fusio_agent');
        if (!$agentTable->hasColumn('public')) {
            $agentTable->addColumn('public', 'integer', ['default' => 0]);
            $agentTable->addColumn('temperature', 'integer', ['default' => 100]);
            $agentTable->addColumn('costs', 'integer', ['notnull' => false]);
        }

        if (!$schema->hasTable('fusio_action_tag')) {
            $actionTagTable = $schema->createTable('fusio_action_tag');
            $actionTagTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $actionTagTable->addColumn('commit_id', 'integer');
            $actionTagTable->addColumn('user_id', 'integer');
            $actionTagTable->addColumn('version', 'string');
            $actionTagTable->addColumn('insert_date', 'datetime');
            $actionTagTable->setPrimaryKey(['id']);
            $actionTagTable->addUniqueIndex(['commit_id', 'version']);

            $actionTagTable->addForeignKeyConstraint($schema->getTable('fusio_action_commit'), ['commit_id'], ['id'], [], 'action_tag_commit_id');
            $actionTagTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'action_tag_user_id');
        }

        if (!$schema->hasTable('fusio_schema_tag')) {
            $schemaTagTable = $schema->createTable('fusio_schema_tag');
            $schemaTagTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $schemaTagTable->addColumn('commit_id', 'integer');
            $schemaTagTable->addColumn('user_id', 'integer');
            $schemaTagTable->addColumn('version', 'string');
            $schemaTagTable->addColumn('insert_date', 'datetime');
            $schemaTagTable->setPrimaryKey(['id']);
            $schemaTagTable->addUniqueIndex(['commit_id', 'version']);

            $schemaTagTable->addForeignKeyConstraint($schema->getTable('fusio_schema_commit'), ['commit_id'], ['id'], [], 'schema_tag_commit_id');
            $schemaTagTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'schema_tag_user_id');
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

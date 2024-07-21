<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240721005146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('fusio_test')) {
            $testTable = $schema->createTable('fusio_test');
            $testTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $testTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $testTable->addColumn('category_id', 'integer', ['default' => 1]);
            $testTable->addColumn('operation_id', 'integer', ['notnull' => false]);
            $testTable->addColumn('status', 'integer', ['default' => 1]);
            $testTable->addColumn('message', 'text', ['notnull' => false]);
            $testTable->addColumn('response', 'text', ['notnull' => false]);
            $testTable->addColumn('uri_fragments', 'string', ['length' => 512, 'notnull' => false]);
            $testTable->addColumn('parameters', 'string', ['length' => 512, 'notnull' => false]);
            $testTable->addColumn('headers', 'string', ['length' => 512, 'notnull' => false]);
            $testTable->addColumn('body', 'text', ['notnull' => false]);
            $testTable->setPrimaryKey(['id']);

            $testTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'test_category_id');
            $testTable->addForeignKeyConstraint($schema->getTable('fusio_operation'), ['operation_id'], ['id'], [], 'test_operation_id');
        }

        $actionTable = $schema->getTable('fusio_action');
        if (!$actionTable->hasForeignKey('action_category_id')) {
            $actionTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'action_category_id');
        }

        $cronjobTable = $schema->getTable('fusio_cronjob');
        if (!$cronjobTable->hasForeignKey('cronjob_category_id')) {
            $cronjobTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'cronjob_category_id');
        }

        $eventTable = $schema->getTable('fusio_event');
        if (!$eventTable->hasForeignKey('event_category_id')) {
            $eventTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'event_category_id');
        }

        $operationTable = $schema->getTable('fusio_operation');
        if (!$operationTable->hasForeignKey('operation_category_id')) {
            $operationTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'operation_category_id');
        }

        $schemaTable = $schema->getTable('fusio_schema');
        if (!$schemaTable->hasForeignKey('schema_category_id')) {
            $schemaTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'schema_category_id');
        }

        $scopeTable = $schema->getTable('fusio_scope');
        if (!$scopeTable->hasForeignKey('scope_category_id')) {
            $scopeTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'scope_category_id');
        }

        $tokenTable = $schema->getTable('fusio_token');
        if (!$tokenTable->hasForeignKey('token_category_id')) {
            $tokenTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'token_category_id');
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

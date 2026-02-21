<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Table;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218181413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $needsCreate = false;
        if ($schema->hasTable('fusio_agent')) {
            $agentTable = $schema->getTable('fusio_agent');
            if (!$agentTable->hasColumn('introduction')) {
                $schema->dropTable('fusio_agent');
                $needsCreate = true;
            }
        } else {
            $needsCreate = true;
        }

        if ($needsCreate) {
            $agentTable = $schema->createTable('fusio_agent');
            $agentTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $agentTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $agentTable->addColumn('category_id', 'integer', ['default' => 1]);
            $agentTable->addColumn('connection_id', 'integer');
            $agentTable->addColumn('status', 'integer', ['default' => Table\Agent::STATUS_ACTIVE]);
            $agentTable->addColumn('name', 'string');
            $agentTable->addColumn('description', 'string');
            $agentTable->addColumn('introduction', 'text');
            $agentTable->addColumn('messages', 'text');
            $agentTable->addColumn('tools', 'text');
            $agentTable->addColumn('outgoing', 'string', ['length' => 255]);
            $agentTable->addColumn('action', 'string', ['length' => 255]);
            $agentTable->addColumn('metadata', 'text', ['notnull' => false]);
            $agentTable->addColumn('insert_date', 'datetime');
            $agentTable->setPrimaryKey(['id']);
            $agentTable->addUniqueIndex(['tenant_id', 'name']);

            $agentTable->addForeignKeyConstraint($schema->getTable('fusio_category'), ['category_id'], ['id'], [], 'agent_category_id');
            $agentTable->addForeignKeyConstraint($schema->getTable('fusio_connection'), ['connection_id'], ['id'], [], 'agent_connection_id');
        }

        if (!$schema->hasTable('fusio_agent_message')) {
            $agentMessageTable = $schema->createTable('fusio_agent_message');
            $agentMessageTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $agentMessageTable->addColumn('agent_id', 'integer');
            $agentMessageTable->addColumn('user_id', 'integer');
            $agentMessageTable->addColumn('parent_id', 'integer', ['notnull' => false, 'default' => null]);
            $agentMessageTable->addColumn('origin', 'integer');
            $agentMessageTable->addColumn('content', 'text');
            $agentMessageTable->addColumn('insert_date', 'datetime');
            $agentMessageTable->setPrimaryKey(['id']);

            $agentMessageTable->addForeignKeyConstraint($schema->getTable('fusio_agent'), ['agent_id'], ['id'], [], 'agent_message_agent_id');
            $agentMessageTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'agent_message_user_id');
            $agentMessageTable->addForeignKeyConstraint($schema->getTable('fusio_agent_message'), ['parent_id'], ['id'], [], 'agent_message_parent_id');
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

<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260507203029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('fusio_agent_task')) {
            $agentTaskTable = $schema->createTable('fusio_agent_task');
            $agentTaskTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $agentTaskTable->addColumn('agent_id', 'integer');
            $agentTaskTable->addColumn('user_id', 'integer');
            $agentTaskTable->addColumn('context_id', 'string', ['length' => 64, 'notnull' => false]);
            $agentTaskTable->addColumn('status', 'integer', ['default' => 1]); // 1: submitted, 2: working, 3: requires_action, 4: completed, 5: failed
            $agentTaskTable->addColumn('input', 'text', ['notnull' => false]);
            $agentTaskTable->addColumn('output', 'text', ['notnull' => false]);
            $agentTaskTable->addColumn('pending_data', 'text', ['notnull' => false]);
            $agentTaskTable->addColumn('update_date', 'datetime');
            $agentTaskTable->addColumn('insert_date', 'datetime');
            $agentTaskTable->setPrimaryKey(['id']);
            $agentTaskTable->addIndex(['context_id']);

            $agentTaskTable->addForeignKeyConstraint($schema->getTable('fusio_agent'), ['agent_id'], ['id'], [], 'agent_task_agent_id');
            $agentTaskTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'agent_task_user_id');
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

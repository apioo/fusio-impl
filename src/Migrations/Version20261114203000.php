<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;
use Fusio\Impl\Table;

/**
 * Adds the agent result action and the agent task table which executes the result action in the background
 */
final class Version20261114203000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the agent action and agent task table';
    }

    public function up(Schema $schema): void
    {
        $agentTable = $schema->getTable('fusio_agent');
        if (!$agentTable->hasColumn('action')) {
            $agentTable->addColumn('action', 'string', ['notnull' => false]);
        }

        if ($agentTable->hasColumn('temperature')) {
            $agentTable->dropColumn('temperature');
        }

        if (!$schema->hasTable('fusio_agent_task')) {
            $agentTaskTable = $schema->createTable('fusio_agent_task');
            $agentTaskTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $agentTaskTable->addColumn('agent_id', 'integer');
            $agentTaskTable->addColumn('user_id', 'integer');
            $agentTaskTable->addColumn('connection_id', 'integer', ['notnull' => false, 'default' => null]);
            $agentTaskTable->addColumn('status', 'integer', ['default' => Table\Agent\Task::STATUS_PENDING]);
            $agentTaskTable->addColumn('item', 'text');
            $agentTaskTable->addColumn('steps', 'text', ['notnull' => false, 'default' => null]);
            $agentTaskTable->addColumn('message', 'text', ['notnull' => false, 'default' => null]);
            $agentTaskTable->addColumn('start_date', 'datetime', ['notnull' => false, 'default' => null]);
            $agentTaskTable->addColumn('end_date', 'datetime', ['notnull' => false, 'default' => null]);
            $agentTaskTable->addColumn('insert_date', 'datetime');
            $agentTaskTable->setPrimaryKey(['id']);
            $agentTaskTable->addIndex(['agent_id', 'user_id']);

            $agentTaskTable->addForeignKeyConstraint($schema->getTable('fusio_agent'), ['agent_id'], ['id'], [], 'agent_task_agent_id');
            $agentTaskTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'agent_task_user_id');
        }

        if (!$schema->hasTable('fusio_log_message')) {
            $logMessageTable = $schema->createTable('fusio_log_message');
            $logMessageTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $logMessageTable->addColumn('log_id', 'integer');
            $logMessageTable->addColumn('level', 'integer');
            $logMessageTable->addColumn('message', 'string', ['length' => 500]);
            $logMessageTable->addColumn('context', 'text');
            $logMessageTable->addColumn('insert_date', 'datetime', ['notnull' => false]);
            $logMessageTable->setPrimaryKey(['id']);
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
        // renamed operations, we rename them in place so that existing scope assignments are preserved
        $renames = [
            'backend.log.getAllErrors' => 'backend.error.getAll',
            'backend.log.getError' => 'backend.error.get',
        ];

        foreach ($renames as $oldName => $newName) {
            $exists = $this->connection->fetchOne('SELECT id FROM fusio_operation WHERE tenant_id IS NULL AND name = :name', ['name' => $newName]);
            if (empty($exists)) {
                $this->connection->executeStatement('UPDATE fusio_operation SET name = :new WHERE tenant_id IS NULL AND name = :old', ['new' => $newName, 'old' => $oldName]);
            }
        }

        // updates existing operations and inserts missing ones
        DataSyncronizer::sync($this->connection);
    }
}

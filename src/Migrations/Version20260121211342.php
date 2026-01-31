<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260121211342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('fusio_agent')) {
            $agentTable = $schema->createTable('fusio_agent');
            $agentTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $agentTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $agentTable->addColumn('user_id', 'integer');
            $agentTable->addColumn('connection_id', 'integer');
            $agentTable->addColumn('origin', 'integer');
            $agentTable->addColumn('intent', 'integer');
            $agentTable->addColumn('message', 'text');
            $agentTable->addColumn('insert_date', 'datetime');
            $agentTable->setPrimaryKey(['id']);

            $agentTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'agent_user_id');
            $agentTable->addForeignKeyConstraint($schema->getTable('fusio_connection'), ['connection_id'], ['id'], [], 'agent_connection_id');
        }

        $mcpSessionTable = $schema->getTable('fusio_mcp_session');
        if (!$mcpSessionTable->hasColumn('insert_date')) {
            $mcpSessionTable->addColumn('update_date', 'datetime', ['notnull' => false]);
            $mcpSessionTable->addColumn('insert_date', 'datetime', ['notnull' => false]);
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

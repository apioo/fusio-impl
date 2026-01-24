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
        if (!$schema->hasTable('fusio_agent_chat')) {
            $agentChatTable = $schema->createTable('fusio_agent_chat');
            $agentChatTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $agentChatTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $agentChatTable->addColumn('user_id', 'integer');
            $agentChatTable->addColumn('connection_id', 'integer');
            $agentChatTable->addColumn('type', 'integer');
            $agentChatTable->addColumn('message', 'text');
            $agentChatTable->addColumn('insert_date', 'datetime');
            $agentChatTable->setPrimaryKey(['id']);

            $agentChatTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'agent_chat_user_id');
            $agentChatTable->addForeignKeyConstraint($schema->getTable('fusio_connection'), ['connection_id'], ['id'], [], 'agent_chat_connection_id');
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

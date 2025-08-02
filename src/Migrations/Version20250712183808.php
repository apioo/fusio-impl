<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250712183808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('fusio_mcp_session')) {
            $mcpSessionTable = $schema->createTable('fusio_mcp_session');
            $mcpSessionTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $mcpSessionTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $mcpSessionTable->addColumn('session_id', 'string', ['length' => 128]);
            $mcpSessionTable->addColumn('data', 'text');
            $mcpSessionTable->setPrimaryKey(['id']);
            $mcpSessionTable->addUniqueIndex(['tenant_id', 'session_id']);
        }

        $appCodeTable = $schema->getTable('fusio_app_code');
        if ($appCodeTable instanceof Table && $appCodeTable->hasColumn('scope')) {
            $appCodeTable->getColumn('scope')->setLength(1023);
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

        // deactivate legacy operations
        $names = [
            'backend.connection.getIntrospection',
            'backend.connection.getIntrospectionForEntity',
            'backend.database.getConnections',
            'backend.database.getTables',
            'backend.database.getTable',
            'backend.database.createTable',
            'backend.database.updateTable',
            'backend.database.deleteTable',
            'backend.database.getRows',
            'backend.database.getRow',
            'backend.database.createRow',
            'backend.database.updateRow',
            'backend.database.deleteRow',
            'backend.schema.updateForm',
        ];

        foreach ($names as $name) {
            $this->connection->update('fusio_operation', [
                'status' => 0,
            ], [
                'name' => $name,
            ]);
        }
    }
}

<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

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
    }

    public function down(Schema $schema): void
    {
    }

    public function isTransactional(): bool
    {
        return false;
    }
}

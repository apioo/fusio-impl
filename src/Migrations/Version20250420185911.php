<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Installation\DataSyncronizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250420185911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('fusio_firewall')) {
            $firewallTable = $schema->createTable('fusio_firewall');
            $firewallTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $firewallTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $firewallTable->addColumn('status', 'integer');
            $firewallTable->addColumn('name', 'string', ['length' => 64]);
            $firewallTable->addColumn('type', 'integer'); // allow/deny
            $firewallTable->addColumn('ip', 'string', ['length' => 39]);
            $firewallTable->addColumn('expire', 'datetime', ['notnull' => false]);
            $firewallTable->addColumn('metadata', 'text', ['notnull' => false]);
            $firewallTable->setPrimaryKey(['id']);
            $firewallTable->addUniqueIndex(['tenant_id', 'name']);
            $firewallTable->addIndex(['tenant_id', 'ip', 'expire']);
        }

        if (!$schema->hasTable('fusio_firewall_log')) {
            $firewallLogTable = $schema->createTable('fusio_firewall_log');
            $firewallLogTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $firewallLogTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $firewallLogTable->addColumn('ip', 'string', ['length' => 39]);
            $firewallLogTable->addColumn('response_code', 'integer');
            $firewallLogTable->addColumn('insert_date', 'datetime', ['notnull' => false]);
            $firewallLogTable->setPrimaryKey(['id']);
            $firewallLogTable->addUniqueIndex(['tenant_id', 'ip']);
        }

        $logTable = $schema->getTable('fusio_log');
        if (!$logTable->hasColumn('response_code')) {
            $logTable->addColumn('response_code', 'integer', ['notnull' => false, 'default' => null]);
            $logTable->addIndex(['tenant_id', 'ip', 'response_code', 'date'], 'IDX_LOG_TIRD');
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

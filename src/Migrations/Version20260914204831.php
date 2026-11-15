<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260914204831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $agentMessageTable = $schema->getTable('fusio_agent_message');
        if (!$agentMessageTable->hasColumn('ref_id')) {
            $agentMessageTable->addColumn('ref_id', 'integer', ['notnull' => false, 'default' => null]);
        }

        $agentTable = $schema->getTable('fusio_agent');
        if (!$agentTable->hasColumn('introduction_action')) {
            $agentTable->addColumn('introduction_action', 'string', ['notnull' => false]);
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

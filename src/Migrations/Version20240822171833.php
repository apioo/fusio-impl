<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240822171833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $logTable = $schema->getTable('fusio_log');
        if ($logTable->hasIndex('IDX_LOG_CID')) {
            $logTable->dropIndex('IDX_LOG_CID');
        }

        if (!$logTable->hasIndex('IDX_LOG_TID')) {
            $logTable->addIndex(['tenant_id', 'ip', 'date'], 'IDX_LOG_TID');
        }

        if (!$logTable->hasIndex('IDX_LOG_TUD')) {
            $logTable->addIndex(['tenant_id', 'user_id', 'date'], 'IDX_LOG_TUD');
        }

        $indexes = $logTable->getIndexes();
        foreach ($indexes as $index) {
            if ($index->getColumns() === ['tenant_id']) {
                $logTable->dropIndex($index->getName());
            }
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

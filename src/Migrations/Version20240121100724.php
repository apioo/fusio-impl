<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Impl\Service\Tenant;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240121100724 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        foreach (Tenant::TENANT_TABLES as $tableName) {
            $table = $schema->getTable($tableName);
            if (!$table->hasColumn('tenant_id')) {
                $table->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
                $table->addIndex(['tenant_id']);
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

<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

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
        $tables = [
            'fusio_action',
            'fusio_app',
            'fusio_audit',
            'fusio_category',
            'fusio_config',
            'fusio_connection',
            'fusio_cronjob',
            'fusio_event',
            'fusio_identity',
            'fusio_log',
            'fusio_operation',
            'fusio_page',
            'fusio_plan',
            'fusio_rate',
            'fusio_role',
            'fusio_schema',
            'fusio_scope',
            'fusio_transaction',
            'fusio_user',
        ];

        foreach ($tables as $tableName) {
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

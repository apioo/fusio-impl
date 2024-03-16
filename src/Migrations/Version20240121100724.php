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

        if ($schema->hasTable('fusio_app_token')) {
            $tokenTable = $schema->getTable('fusio_app_token');
            $tokenTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $tokenTable->addIndex(['tenant_id']);

            $schema->renameTable('fusio_app_token', 'fusio_token');
        }

        if ($schema->hasTable('fusio_event_subscription')) {
            $webhookTable = $schema->getTable('fusio_event_subscription');
            $webhookTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $webhookTable->addColumn('name', 'string', ['length' => 32]);
            $webhookTable->addIndex(['tenant_id']);

            $schema->renameTable('fusio_event_subscription', 'fusio_webhook');
        }

        if ($schema->hasTable('fusio_event_response')) {
            $webhookResponseTable = $schema->getTable('fusio_event_response');
            $webhookResponseTable->addColumn('webhook_id', 'integer');
            $webhookResponseTable->dropColumn('subscription_id');
            $webhookResponseTable->addForeignKeyConstraint($schema->getTable('fusio_webhook'), ['webhook_id'], ['id'], [], 'webhook_response_webhook_id');

            $schema->renameTable('fusio_event_response', 'fusio_webhook_response');
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

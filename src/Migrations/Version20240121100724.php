<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
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
        if ($schema->hasTable('fusio_app_token')) {
            //$schema->dropTable('fusio_app_token');

            $tokenTable = $schema->createTable('fusio_token');
            $tokenTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $tokenTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $tokenTable->addColumn('category_id', 'integer', ['default' => 1]);
            $tokenTable->addColumn('app_id', 'integer', ['notnull' => false]);
            $tokenTable->addColumn('user_id', 'integer');
            $tokenTable->addColumn('status', 'integer', ['default' => 1]);
            $tokenTable->addColumn('name', 'string', ['length' => 255, 'notnull' => false]);
            $tokenTable->addColumn('token', 'string', ['length' => 512]);
            $tokenTable->addColumn('refresh', 'string', ['length' => 255, 'notnull' => false]);
            $tokenTable->addColumn('scope', 'string', ['length' => 1023]);
            $tokenTable->addColumn('ip', 'string', ['length' => 40]);
            $tokenTable->addColumn('expire', 'datetime', ['notnull' => false]);
            $tokenTable->addColumn('date', 'datetime');
            $tokenTable->setPrimaryKey(['id']);
            $tokenTable->addUniqueIndex(['tenant_id', 'status', 'token']);
            $tokenTable->addUniqueIndex(['tenant_id', 'refresh']);

            $tokenTable->addForeignKeyConstraint($schema->getTable('fusio_app'), ['app_id'], ['id'], [], 'token_app_id');
            $tokenTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'token_user_id');
        }

        if ($schema->hasTable('fusio_event_subscription')) {
            //$schema->dropTable('fusio_event_subscription');

            $webhookTable = $schema->createTable('fusio_webhook');
            $webhookTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $webhookTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $webhookTable->addColumn('event_id', 'integer');
            $webhookTable->addColumn('user_id', 'integer');
            $webhookTable->addColumn('status', 'integer');
            $webhookTable->addColumn('name', 'string', ['length' => 32]);
            $webhookTable->addColumn('endpoint', 'string', ['length' => 255]);
            $webhookTable->setPrimaryKey(['id']);

            $webhookTable->addForeignKeyConstraint($schema->getTable('fusio_event'), ['event_id'], ['id'], [], 'webhook_event_id');
            $webhookTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'webhook_user_id');
        }

        if ($schema->hasTable('fusio_event_response')) {
            //$schema->dropTable('fusio_event_response');

            $webhookResponseTable = $schema->createTable('fusio_webhook_response');
            $webhookResponseTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $webhookResponseTable->addColumn('webhook_id', 'integer');
            $webhookResponseTable->addColumn('status', 'integer');
            $webhookResponseTable->addColumn('attempts', 'integer');
            $webhookResponseTable->addColumn('code', 'integer', ['notnull' => false]);
            $webhookResponseTable->addColumn('body', 'text', ['notnull' => false]);
            $webhookResponseTable->addColumn('execute_date', 'datetime', ['notnull' => false]);
            $webhookResponseTable->addColumn('insert_date', 'datetime');
            $webhookResponseTable->setPrimaryKey(['id']);

            $webhookResponseTable->addForeignKeyConstraint($schema->getTable('fusio_webhook'), ['webhook_id'], ['id'], [], 'webhook_response_webhook_id');
        }

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

    public function postUp(Schema $schema): void
    {
        parent::postUp($schema);

        $schemaManager = $this->connection->createSchemaManager();
        $tableNames = $schemaManager->listTableNames();

        if (in_array('fusio_app_token', $tableNames)) {
            $this->connection->executeQuery('INSERT INTO fusio_token SELECT id, app_id, user_id, null AS tenant_id, 2 AS category_id, status, null AS name, token, refresh, scope, ip, expire, date FROM fusio_app_token');
            $this->dropTable($schemaManager, 'fusio_app_token');
        }

        if (in_array('fusio_event_subscription', $tableNames)) {
            $this->connection->executeQuery('INSERT INTO fusio_webhook SELECT id, event_id, user_id, null AS tenant_id, status, \'Webhook\' AS name, endpoint FROM fusio_event_subscription');
            $this->dropTable($schemaManager, 'fusio_event_subscription');
        }

        if (in_array('fusio_event_response', $tableNames)) {
            $this->connection->executeQuery('INSERT INTO fusio_webhook_response SELECT id, subscription_id AS webhook_id, status, attempts, code, body, execute_date, insert_date FROM fusio_event_response');
            $this->dropTable($schemaManager, 'fusio_event_response');
        }
    }

    private function dropTable(AbstractSchemaManager $schemaManager, string $tableName): void
    {
        $foreignKeys = $schemaManager->listTableForeignKeys($tableName);
        foreach ($foreignKeys as $foreignKey) {
            $schemaManager->dropForeignKey($foreignKey, $tableName);
        }

        $this->connection->createSchemaManager()->dropTable($tableName);
    }
}

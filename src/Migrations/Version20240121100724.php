<?php

declare(strict_types=1);

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\Migrations\AbstractMigration;
use Fusio\Engine\Inflection\ClassName;
use Fusio\Impl\Backend\Action\Connection\RenewToken;
use Fusio\Impl\Installation\DataSyncronizer;
use Fusio\Impl\Service\Tenant;
use Fusio\Impl\Table\Operation;

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
            $webhookTable = $schema->createTable('fusio_webhook');
            $webhookTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $webhookTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $webhookTable->addColumn('event_id', 'integer');
            $webhookTable->addColumn('user_id', 'integer');
            $webhookTable->addColumn('status', 'integer');
            $webhookTable->addColumn('name', 'string', ['length' => 32]);
            $webhookTable->addColumn('endpoint', 'string', ['length' => 255]);
            $webhookTable->setPrimaryKey(['id']);
            $webhookTable->addIndex(['tenant_id']);

            $webhookTable->addForeignKeyConstraint($schema->getTable('fusio_event'), ['event_id'], ['id'], [], 'webhook_event_id');
            $webhookTable->addForeignKeyConstraint($schema->getTable('fusio_user'), ['user_id'], ['id'], [], 'webhook_user_id');
        }

        if ($schema->hasTable('fusio_event_response')) {
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

                if ($tableName === 'fusio_app') {
                    $this->dropIndexForColumn($table, ['app_key']);
                    $table->addUniqueIndex(['tenant_id', 'app_key']);
                } elseif ($tableName === 'fusio_operation') {
                    $this->dropIndexForColumn($table, ['name']);
                    $this->dropIndexForColumn($table, ['http_method', 'http_path']);
                    $table->addUniqueIndex(['tenant_id', 'name']);
                    $table->addUniqueIndex(['tenant_id', 'http_method', 'http_path']);
                } elseif ($tableName === 'fusio_page') {
                    $this->dropIndexForColumn($table, ['slug']);
                    $table->addUniqueIndex(['tenant_id', 'slug']);
                } elseif ($tableName === 'fusio_transaction') {
                    $this->dropIndexForColumn($table, ['transaction_id']);
                    $table->addUniqueIndex(['tenant_id', 'transaction_id']);
                } elseif ($tableName === 'fusio_user') {
                    $this->dropIndexForColumn($table, ['identity_id', 'remote_id']);
                    $this->dropIndexForColumn($table, ['name']);
                    $this->dropIndexForColumn($table, ['email']);
                    $table->addUniqueIndex(['tenant_id', 'identity_id', 'remote_id']);
                    $table->addUniqueIndex(['tenant_id', 'name']);
                    $table->addUniqueIndex(['tenant_id', 'email']);
                } elseif (in_array($tableName, ['fusio_audit', 'fusio_log'])) {
                    $table->addIndex(['tenant_id']);
                } elseif (in_array($tableName, ['fusio_token', 'fusio_webhook'])) {
                    // noop
                } else {
                    $this->dropIndexForColumn($table, ['name']);
                    $table->addUniqueIndex(['tenant_id', 'name']);
                }
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
        }

        if (in_array('fusio_event_subscription', $tableNames)) {
            $this->connection->executeQuery('INSERT INTO fusio_webhook SELECT id, event_id, user_id, null AS tenant_id, status, \'Webhook\' AS name, endpoint FROM fusio_event_subscription');
        }

        if (in_array('fusio_event_response', $tableNames)) {
            $this->connection->executeQuery('INSERT INTO fusio_webhook_response SELECT id, subscription_id AS webhook_id, status, attempts, code, body, execute_date, insert_date FROM fusio_event_response');
        }

        if (in_array('fusio_app_token', $tableNames)) {
            $this->dropTable($schemaManager, 'fusio_app_token');
        }

        if (in_array('fusio_event_response', $tableNames)) {
            $this->dropTable($schemaManager, 'fusio_event_response');
        }

        if (in_array('fusio_event_subscription', $tableNames)) {
            $this->dropTable($schemaManager, 'fusio_event_subscription');
        }

        DataSyncronizer::sync($this->connection);

        // deactivate legacy operations
        $operations = [
            '/backend/app/token',
            '/backend/app/token/$token_id<[0-9]+>',
            '/backend/event/subscription',
            '/backend/event/subscription/$subscription_id<[0-9]+>',
            '/consumer/subscription',
            '/consumer/subscription/$subscription_id<[0-9]+>',
        ];

        foreach ($operations as $httpPath) {
            $this->connection->update('fusio_operation', ['status' => Operation::STATUS_DELETED], ['http_path' => $httpPath]);
        }

        // migrate legacy cronjobs
        $cronjobs = [
            'Backend_Action_Action_Async' => null,
            'Backend_Action_Event_Execute' => null,
            'Backend_Action_Connection_RenewToken' => RenewToken::class,
        ];

        foreach ($cronjobs as $oldAction => $newAction) {
            if ($newAction === null) {
                $this->connection->delete('fusio_cronjob', ['action' => $oldAction]);
            } else {
                $this->connection->update('fusio_cronjob', ['action' => ClassName::serialize($newAction)], ['action' => $oldAction]);
            }
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

    private function dropIndexForColumn(Table $table, array $columns): void
    {
        $indexes = $table->getIndexes();
        foreach ($indexes as $index) {
            if ($index->getColumns() === $columns) {
                $table->dropIndex($index->getName());
            }
        }
    }
}

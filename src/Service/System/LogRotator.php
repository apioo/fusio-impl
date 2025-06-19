<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Service\System;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;

/**
 * To keep the system fast we rotate specific tables which get otherwise really large over time. We simply copy all
 * table data to an archive table and truncate the table. Through this a user can still investigate all logs. If
 * the space is required a user can also simply drop those archive tables
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class LogRotator
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function rotate(): \Generator
    {
        $schemaManager = $this->connection->createSchemaManager();
        $schema = $schemaManager->introspectSchema();

        yield from $this->archiveAuditTable($schemaManager, $schema);
        yield from $this->archiveLogErrorTable($schemaManager, $schema);
        yield from $this->archiveLogTable($schemaManager, $schema);
        yield from $this->archiveCronjobErrorTable($schemaManager, $schema);
    }

    private function archiveAuditTable(AbstractSchemaManager $schemaManager, Schema $schema): \Generator
    {
        $tableName = 'fusio_audit_' . date('Ymd');

        // create archive table
        if (!$schema->hasTable($tableName)) {
            $auditTable = $schema->createTable($tableName);
            $auditTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $auditTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $auditTable->addColumn('app_id', 'integer');
            $auditTable->addColumn('user_id', 'integer');
            $auditTable->addColumn('ref_id', 'integer', ['notnull' => false]);
            $auditTable->addColumn('event', 'string');
            $auditTable->addColumn('ip', 'string', ['length' => 40]);
            $auditTable->addColumn('message', 'string');
            $auditTable->addColumn('content', 'text', ['notnull' => false]);
            $auditTable->addColumn('date', 'datetime');
            $auditTable->setPrimaryKey(['id']);
            $auditTable->addOption('engine', 'MyISAM');

            $schemaManager->createTable($auditTable);

            yield 'Created archive table ' . $tableName;
        }

        yield from $this->copy('fusio_audit', $tableName, ['id', 'tenant_id', 'app_id', 'user_id', 'ref_id', 'event', 'ip', 'message', 'content', 'date']);
    }

    private function archiveLogTable(AbstractSchemaManager $schemaManager, Schema $schema): \Generator
    {
        $tableName = 'fusio_log_' . date('Ymd');

        // create archive table
        if (!$schema->hasTable($tableName)) {
            $logTable = $schema->createTable($tableName);
            $logTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $logTable->addColumn('tenant_id', 'string', ['length' => 64, 'notnull' => false, 'default' => null]);
            $logTable->addColumn('operation_id', 'integer', ['notnull' => false]);
            $logTable->addColumn('app_id', 'integer', ['notnull' => false]);
            $logTable->addColumn('user_id', 'integer', ['notnull' => false]);
            $logTable->addColumn('ip', 'string', ['length' => 40]);
            $logTable->addColumn('user_agent', 'string', ['length' => 255]);
            $logTable->addColumn('method', 'string', ['length' => 16]);
            $logTable->addColumn('path', 'string', ['length' => 1023]);
            $logTable->addColumn('header', 'text');
            $logTable->addColumn('body', 'text', ['notnull' => false]);
            $logTable->addColumn('execution_time', 'integer', ['notnull' => false, 'default' => null]);
            $logTable->addColumn('date', 'datetime');
            $logTable->setPrimaryKey(['id']);
            $logTable->addOption('engine', 'MyISAM');

            $schemaManager->createTable($logTable);

            yield 'Created archive table ' . $tableName;
        }

        yield from $this->copy('fusio_log', $tableName, ['id', 'tenant_id', 'operation_id', 'app_id', 'user_id', 'ip', 'user_agent', 'method', 'path', 'header', 'body', 'execution_time', 'date']);
    }

    private function archiveLogErrorTable(AbstractSchemaManager $schemaManager, Schema $schema): \Generator
    {
        $tableName = 'fusio_log_error_' . date('Ymd');

        // create archive table
        if (!$schema->hasTable($tableName)) {
            $logErrorTable = $schema->createTable($tableName);
            $logErrorTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $logErrorTable->addColumn('log_id', 'integer');
            $logErrorTable->addColumn('message', 'string', ['length' => 500]);
            $logErrorTable->addColumn('trace', 'text');
            $logErrorTable->addColumn('file', 'string', ['length' => 255]);
            $logErrorTable->addColumn('line', 'integer');
            $logErrorTable->addColumn('insert_date', 'datetime', ['notnull' => false]);
            $logErrorTable->setPrimaryKey(['id']);
            $logErrorTable->addOption('engine', 'MyISAM');

            $schemaManager->createTable($logErrorTable);

            yield 'Created archive table ' . $tableName;
        }

        yield from $this->copy('fusio_log_error', $tableName, ['id', 'log_id', 'message', 'trace', 'file', 'line', 'insert_date']);
    }

    private function archiveCronjobErrorTable(AbstractSchemaManager $schemaManager, Schema $schema): \Generator
    {
        $tableName = 'fusio_cronjob_error_' . date('Ymd');

        // create archive table
        if (!$schema->hasTable($tableName)) {
            $cronjobErrorTable = $schema->createTable($tableName);
            $cronjobErrorTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $cronjobErrorTable->addColumn('cronjob_id', 'integer');
            $cronjobErrorTable->addColumn('message', 'string', ['length' => 500]);
            $cronjobErrorTable->addColumn('trace', 'text');
            $cronjobErrorTable->addColumn('file', 'string', ['length' => 255]);
            $cronjobErrorTable->addColumn('line', 'integer');
            $cronjobErrorTable->addColumn('insert_date', 'datetime', ['notnull' => false]);
            $cronjobErrorTable->setPrimaryKey(['id']);
            $cronjobErrorTable->addOption('engine', 'MyISAM');

            $schemaManager->createTable($cronjobErrorTable);

            yield 'Created archive table ' . $tableName;
        }

        yield from $this->copy('fusio_cronjob_error', $tableName, ['id', 'cronjob_id', 'message', 'trace', 'file', 'line', 'insert_date']);
    }

    private function copy(string $sourceTable, string $archiveTable, array $columns): \Generator
    {
        $count = $this->connection->executeStatement('INSERT INTO ' . $archiveTable . ' SELECT ' . implode(', ', $columns) . ' FROM ' . $sourceTable);
        yield 'Copied ' . $count . ' entries to ' . $archiveTable . ' table';

        // truncate table
        $this->connection->executeStatement('DELETE FROM ' . $sourceTable . ' WHERE 1=1');
        yield 'Truncated ' . $sourceTable . ' table';
    }
}

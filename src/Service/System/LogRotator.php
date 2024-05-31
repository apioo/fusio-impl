<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * LogRotator
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

            yield 'Created audit archive table ' . $tableName;
        }

        // copy all data to archive table
        $result = $this->connection->fetchAllAssociative('SELECT tenant_id, app_id, user_id, ref_id, event, ip, message, content, date FROM fusio_audit');
        foreach ($result as $row) {
            $this->connection->insert($tableName, $row);
        }

        yield 'Copied ' . count($result) . ' entries to audit archive table';

        // truncate table
        $this->connection->executeStatement('DELETE FROM fusio_audit WHERE 1=1');

        yield 'Truncated audit table';
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

            yield 'Created log archive table ' . $tableName;
        }

        // copy all data to archive table
        $result = $this->connection->fetchAllAssociative('SELECT tenant_id, operation_id, app_id, user_id, ip, user_agent, method, path, header, body, execution_time, date FROM fusio_log');
        foreach ($result as $row) {
            $this->connection->insert($tableName, $row);
        }

        yield 'Copied ' . count($result) . ' entries to log archive table';

        // truncate table
        $this->connection->executeStatement('DELETE FROM fusio_log WHERE 1=1');

        yield 'Truncated log table';
    }

    private function archiveLogErrorTable(AbstractSchemaManager $schemaManager, Schema $schema): \Generator
    {
        $tableName = 'fusio_log_error_' . date('Ymd');

        // create archive table
        if (!$schema->hasTable($tableName)) {
            $logErrorTable = $schema->createTable('fusio_log_error');
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

            yield 'Created log archive table ' . $tableName;
        }

        // copy all data to archive table
        $result = $this->connection->fetchAllAssociative('SELECT log_id, message, trace, file, line, insert_date FROM fusio_log_error');
        foreach ($result as $row) {
            $this->connection->insert($tableName, $row);
        }

        yield 'Copied ' . count($result) . ' entries to log error archive table';

        // truncate table
        $this->connection->executeStatement('DELETE FROM fusio_log_error WHERE 1=1');

        yield 'Truncated log error table';
    }

    private function archiveCronjobErrorTable(AbstractSchemaManager $schemaManager, Schema $schema): \Generator
    {
        $tableName = 'fusio_cronjob_error_' . date('Ymd');

        // create archive table
        if (!$schema->hasTable($tableName)) {
            $cronjobErrorTable = $schema->createTable('fusio_cronjob_error');
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

            yield 'Created cronjob error archive table ' . $tableName;
        }

        // copy all data to archive table
        $result = $this->connection->fetchAllAssociative('SELECT cronjob_id, message, trace, file, line, insert_date FROM fusio_cronjob_error');
        foreach ($result as $row) {
            $this->connection->insert($tableName, $row);
        }

        yield 'Copied ' . count($result) . ' entries to cronjob error archive table';

        // truncate table
        $this->connection->executeStatement('DELETE FROM fusio_cronjob_error WHERE 1=1');

        yield 'Truncated cronjob error table';
    }
}

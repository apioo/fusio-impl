<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Service\System;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;

/**
 * LogRotator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
        $schemaManager = $this->connection->getSchemaManager();
        $schema = $schemaManager->createSchema();

        yield from $this->archiveAuditTable($schemaManager, $schema);
        yield from $this->archiveLogTable($schemaManager, $schema);
    }

    private function archiveAuditTable(AbstractSchemaManager $schemaManager, Schema $schema): \Generator
    {
        $tableName = 'fusio_audit_' . date('Ymd');

        // create archive table
        if (!$schema->hasTable($tableName)) {
            $auditTable = $schema->createTable($tableName);
            $auditTable->addColumn('id', 'integer', ['autoincrement' => true]);
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
        $result = $this->connection->fetchAllAssociative('SELECT app_id, user_id, ref_id, event, ip, message, content, date FROM fusio_audit');
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
            $logTable->addColumn('route_id', 'integer', ['notnull' => false]);
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
        $result = $this->connection->fetchAllAssociative('SELECT route_id, app_id, user_id, ip, user_agent, method, path, header, body, execution_time, date FROM fusio_log');
        foreach ($result as $row) {
            $this->connection->insert($tableName, $row);
        }

        yield 'Copied ' . count($result) . ' entries to log archive table';

        // truncate table
        $this->connection->executeStatement('DELETE FROM fusio_log_error WHERE 1=1');
        $this->connection->executeStatement('DELETE FROM fusio_log WHERE 1=1');

        yield 'Truncated log table';
    }
}

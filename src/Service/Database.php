<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Service;

use Doctrine\DBAL\Connection as DBALConnection;
use Doctrine\DBAL\Schema\Table;
use Fusio\Impl\Connector;
use PSX\DateTime;
use PSX\Http\Exception as StatusCode;
use Doctrine\DBAL\Schema\Schema;
use PSX\Sql;
use PSX\Sql\Condition;
use RuntimeException;

/**
 * Database
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Database
{
    protected $connector;
    
    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    public function getTables($connectionId)
    {
        $connection = $this->connector->getConnection($connectionId);
        if ($connection instanceof DBALConnection) {
            $tableNames = $connection->getSchemaManager()->listTableNames();
            $result     = [];

            foreach ($tableNames as $tableName) {
                $result[] = [
                    'name' => $tableName,
                ];
            }

            return [
                'entry' => $result
            ];
        } else {
            throw new StatusCode\NotFoundException('Invalid connection');
        }
    }

    public function getTable($connectionId, $tableName)
    {
        $connection = $this->connector->getConnection($connectionId);
        if ($connection instanceof DBALConnection) {
            $tableNames = $connection->getSchemaManager()->listTableNames();
            
            if (in_array($tableName, $tableNames)) {
                return [
                    'name'    => $tableName,
                    'columns' => $this->getColumns($connection, $tableName),
                    'indexes' => $this->getIndexes($connection, $tableName),
                    'fks'     => $this->getForeignKeys($connection, $tableName),
                ];
            } else {
                throw new StatusCode\NotFoundException('Invalid table');
            }
        } else {
            throw new StatusCode\NotFoundException('Invalid connection');
        }
    }

    public function create($connectionId, $tableName, $columns, $primaryKeys, $uniqueKeys)
    {
        $connection = $this->connector->getConnection($connectionId);
        if ($connection instanceof DBALConnection) {
            $schema = $connection->getSchemaManager()->createSchema();

            $table = $schema->createTable($tableName);
            $this->createTable($table, $columns, $primaryKeys, $uniqueKeys);

            // execute queries
            $fromSchema = $connection->getSchemaManager()->createSchema();
            $queries    = $fromSchema->getMigrateToSql($schema, $connection->getDatabasePlatform());

            foreach ($queries as $query) {
                $connection->query($query);
            }
        } else {
            throw new StatusCode\NotFoundException('Invalid connection');
        }
    }

    public function update($connectionId, $tableName, $columns, $indexes, $uniqueKeys)
    {
        $connection = $this->connector->getConnection($connectionId);
        if ($connection instanceof DBALConnection) {
            $schema = $connection->getSchemaManager()->createSchema();
            $schema->dropTable($tableName);

            $table = $schema->createTable($tableName);
            $this->createTable($table, $columns, $indexes, $uniqueKeys);

            // execute queries
            $fromSchema = $connection->getSchemaManager()->createSchema();
            $queries    = $fromSchema->getMigrateToSql($schema, $connection->getDatabasePlatform());
    
            foreach ($queries as $query) {
                $connection->query($query);
            }
        } else {
            throw new StatusCode\NotFoundException('Invalid connection');
        }
    }

    public function delete($connectionId, $tableName)
    {
        $connection = $this->connector->getConnection($connectionId);
        if ($connection instanceof DBALConnection) {
            $schema = $connection->getSchemaManager()->createSchema();
            $schema->dropTable($tableName);

            // execute queries
            $fromSchema = $connection->getSchemaManager()->createSchema();
            $queries    = $fromSchema->getMigrateToSql($schema, $connection->getDatabasePlatform());

            foreach ($queries as $query) {
                $connection->query($query);
            }
        } else {
            throw new StatusCode\NotFoundException('Invalid connection');
        }
    }

    protected function createTable(Table $table, $columns, $indexes, $foreignKeys)
    {
        $fields = ['notnull', 'default', 'autoincrement', 'length', 'fixed', 'precision', 'scale'];

        foreach ($columns as $column) {
            $options = [];
            foreach ($fields as $field) {
                if (isset($column[$field])) {
                    $options[$field] = $column[$field];
                }
            }

            $table->addColumn($column['name'], $column['type'], $options);
        }

        if (!empty($indexes)) {
            foreach ($indexes as $index) {
                if ($index['primary'] && $index['unique']) {
                    $table->setPrimaryKey($index['columns']);
                } elseif (!$index['primary'] && $index['unique']) {
                    $table->addUniqueIndex($index['columns']);
                } elseif (!$index['primary'] && !$index['unique']) {
                    $table->addIndex($index['columns']);
                }
            }
        }

        if (!empty($foreignKeys)) {
            foreach ($foreignKeys as $foreignKey) {
                $table->addForeignKeyConstraint(
                    $foreignKey['foreignTable'],
                    $foreignKey['columns'],
                    $foreignKey['foreignColumns'],
                    [],
                    $foreignKey['name']
                );
            }
        }

        return $table;
    }

    protected function getColumns(DBALConnection $connection, $tableName)
    {
        $columns = $connection->getSchemaManager()->listTableColumns($tableName);
        $result  = [];

        foreach ($columns as $column) {
            $result[] = [
                'name'    => $column->getName(),
                'type'    => $column->getType()->getName(),
                'length'  => $column->getLength(),
                'null'    => $column->getNotnull() === false,
                'default' => $column->getDefault(),
                'comment' => $column->getComment(),
            ];
        }
        
        return $result;
    }

    protected function getIndexes(DBALConnection $connection, $tableName)
    {
        $indexes = $connection->getSchemaManager()->listTableIndexes($tableName);
        $result  = [];

        foreach ($indexes as $index) {
            $result[] = [
                'name'    => strtoupper($index->getName()),
                'columns' => $index->getColumns(),
                'primary' => $index->isPrimary(),
                'unique'  => $index->isUnique(),
            ];
        }

        return $result;
    }
    
    protected function getForeignKeys(DBALConnection $connection, $tableName)
    {
        $fks    = $connection->getSchemaManager()->listTableForeignKeys($tableName);
        $result = [];

        foreach ($fks as $fk) {
            $result[] = [
                'name'    => $fk->getName(),
                'columns' => $fk->getColumns(),
                'foreignTable'   => $fk->getForeignTableName(),
                'foreignColumns' => $fk->getForeignColumns(),
            ];
        }

        return $result;
    }
}

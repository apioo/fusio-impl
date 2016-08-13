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
use Doctrine\DBAL\Schema\Schema as DBALSchema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Fusio\Impl\Connector;
use PSX\DateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Sql;
use PSX\Sql\Condition;

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
                // skip fusio tables
                if (strpos($tableName, 'fusio_') === 0) {
                    continue;
                }

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
                // skip fusio tables
                if (strpos($tableName, 'fusio_') === 0) {
                    throw new StatusCode\BadRequestException('Invalid table');
                }

                return [
                    'name'        => $tableName,
                    'columns'     => $this->getColumns($connection, $tableName),
                    'indexes'     => $this->getIndexes($connection, $tableName),
                    'foreignKeys' => $this->getForeignKeys($connection, $tableName),
                ];
            } else {
                throw new StatusCode\NotFoundException('Invalid table');
            }
        } else {
            throw new StatusCode\NotFoundException('Invalid connection');
        }
    }

    public function preview($connectionId, $tableName, $columns, $primaryKeys, $uniqueKeys)
    {
        $connection = $this->connector->getConnection($connectionId);
        if ($connection instanceof DBALConnection) {
            // skip fusio tables
            if (strpos($tableName, 'fusio_') === 0) {
                throw new StatusCode\BadRequestException('Invalid table');
            }

            $schema = $connection->getSchemaManager()->createSchema();

            $table = $schema->createTable($tableName);
            $this->createTable($table, $columns, $primaryKeys, $uniqueKeys);

            // generate queries
            $fromSchema = $connection->getSchemaManager()->createSchema();
            $queries    = $fromSchema->getMigrateToSql($schema, $connection->getDatabasePlatform());

            return [
                'queries' => $queries,
            ];
        } else {
            throw new StatusCode\NotFoundException('Invalid connection');
        }
    }

    public function create($connectionId, $tableName, $columns, $primaryKeys, $uniqueKeys, $preview = true)
    {
        $connection = $this->connector->getConnection($connectionId);
        if ($connection instanceof DBALConnection) {
            // skip fusio tables
            if (strpos($tableName, 'fusio_') === 0) {
                throw new StatusCode\BadRequestException('Invalid table');
            }

            $schema = $connection->getSchemaManager()->createSchema();
            $table  = $schema->createTable($tableName);

            $this->createTable($table, $columns, $primaryKeys, $uniqueKeys);

            return $this->executeQuery($connection, $schema, $preview);
        } else {
            throw new StatusCode\NotFoundException('Invalid connection');
        }
    }

    public function update($connectionId, $tableName, $columns, $indexes, $uniqueKeys, $preview = true)
    {
        $connection = $this->connector->getConnection($connectionId);
        if ($connection instanceof DBALConnection) {
            // skip fusio tables
            if (strpos($tableName, 'fusio_') === 0) {
                throw new StatusCode\BadRequestException('Invalid table');
            }

            $schema = $connection->getSchemaManager()->createSchema();
            $table  = $schema->getTable($tableName);

            $this->updateTable($table, $columns, $indexes, $uniqueKeys);

            return $this->executeQuery($connection, $schema, $preview);
        } else {
            throw new StatusCode\NotFoundException('Invalid connection');
        }
    }

    public function delete($connectionId, $tableName, $preview = true)
    {
        $connection = $this->connector->getConnection($connectionId);
        if ($connection instanceof DBALConnection) {
            // skip fusio tables
            if (strpos($tableName, 'fusio_') === 0) {
                throw new StatusCode\BadRequestException('Invalid table');
            }

            $schema = $connection->getSchemaManager()->createSchema();
            $schema->dropTable($tableName);

            return $this->executeQuery($connection, $schema, $preview);
        } else {
            throw new StatusCode\NotFoundException('Invalid connection');
        }
    }

    protected function executeQuery(DBALConnection $connection, DBALSchema $schema, $preview)
    {
        $fromSchema = $connection->getSchemaManager()->createSchema();
        $queries    = $fromSchema->getMigrateToSql($schema, $connection->getDatabasePlatform());

        if ($preview === true) {
            return $queries;
        } else {
            foreach ($queries as $query) {
                $connection->query($query);
            }
            return null;
        }
    }

    protected function createTable(Table $table, $columns, $indexes, $foreignKeys)
    {
        foreach ($columns as $column) {
            $table->addColumn($column['name'], $column['type'], $this->getColumnOptions($column));
        }

        if (!empty($indexes)) {
            foreach ($indexes as $index) {
                if ($index['primary']) {
                    $table->setPrimaryKey($index['columns']);
                } else {
                    if ($index['unique']) {
                        $table->addUniqueIndex($index['columns'], $index['name']);
                    } else {
                        $table->addIndex($index['columns'], $index['name']);
                    }
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

    protected function updateTable(Table $table, $columns, $indexes, $foreignKeys)
    {
        $newColumns = [];
        foreach ($columns as $column) {
            if ($table->hasColumn($column['name'])) {
                $table->changeColumn($column['name'], $this->getColumnOptions($column));
            } else {
                $table->addColumn($column['name'], $column['type'], $this->getColumnOptions($column));
            }

            $newColumns[] = strtolower($column['name']);
        }

        $removeColumns = array_diff(array_keys($table->getColumns()), $newColumns);
        foreach ($removeColumns as $columnName) {
            $table->dropColumn($columnName);
        }

        if (!empty($indexes)) {
            foreach ($indexes as $index) {
                if ($index['primary']) {
                    if ($table->hasPrimaryKey()) {
                        $table->dropPrimaryKey();
                    }

                    $table->setPrimaryKey($index['columns']);
                } else {
                    if ($table->hasIndex($index['name'])) {
                        $table->dropIndex($index['name']);
                    }

                    if ($index['unique']) {
                        $table->addUniqueIndex($index['columns'], $index['name']);
                    } else {
                        $table->addIndex($index['columns'], $index['name']);
                    }
                }
            }
        }

        if (!empty($foreignKeys)) {
            foreach ($foreignKeys as $foreignKey) {
                if ($table->hasForeignKey($foreignKey['name'])) {
                    $table->removeForeignKey($foreignKey['name']);
                }

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

    protected function getColumnOptions($options)
    {
        $fields = ['type', 'length', 'null', 'default', 'autoincrement'];
        $result = [];
        foreach ($fields as $field) {
            if (isset($options[$field])) {
                if ($field === 'type') {
                    $result['type'] = Type::getType($options[$field]);
                } elseif ($field === 'null') {
                    $result['notnull'] = !$options[$field];
                } elseif ($field === 'default') {
                    $result['default'] = !empty($options[$field]) ? $options[$field] : null;
                } else {
                    $result[$field] = $options[$field];
                }
            }
        }
        
        return $result;
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
                'autoincrement' => $column->getAutoincrement(),
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
                'name'           => $fk->getName(),
                'columns'        => $fk->getColumns(),
                'foreignTable'   => $fk->getForeignTableName(),
                'foreignColumns' => $fk->getForeignColumns(),
            ];
        }

        return $result;
    }
}

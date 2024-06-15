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

namespace Fusio\Impl\Backend\Action\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\Connector;
use Fusio\Engine\RequestInterface;
use Fusio\Model\Backend\DatabaseRow;
use Fusio\Model\Backend\DatabaseTable;
use PSX\DateTime\LocalDate;
use PSX\DateTime\LocalDateTime;
use PSX\DateTime\LocalTime;
use PSX\Http\Exception\BadRequestException;
use PSX\Http\Exception\InternalServerErrorException;
use PSX\Http\Exception\NotFoundException;

/**
 * TableAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
abstract class TableAbstract implements ActionInterface
{
    private Connector $connector;

    public function __construct(Connector $connector)
    {
        $this->connector = $connector;
    }

    protected function getConnection(RequestInterface $request): Connection
    {
        $connectionId = $request->get('connection_id');
        if (empty($connectionId)) {
            throw new BadRequestException('Provided no connection');
        }

        $connection = $this->connector->getConnection($connectionId);
        if (!$connection instanceof Connection) {
            throw new BadRequestException('Provided an invalid connection');
        }

        return $connection;
    }

    protected function getTable(RequestInterface $request, AbstractSchemaManager $schemaManager): Table
    {
        $tableName = $request->get('table_name');
        if (empty($tableName)) {
            throw new BadRequestException('Provided an no table');
        }

        if (!preg_match('/^[A-Za-z0-9_]+$/', $tableName)) {
            throw new BadRequestException('Provided an invalid table');
        }

        if (!$schemaManager->tablesExist($tableName)) {
            throw new NotFoundException('Provided table does not exist');
        }

        return $schemaManager->introspectTable($tableName);
    }

    protected function getPrimaryKeyColumn(Table $table): string
    {
        $primaryKey = $table->getPrimaryKey();
        if (!$primaryKey instanceof Index) {
            throw new InternalServerErrorException('Provided table has no primary key');
        }

        $columns = $primaryKey->getColumns();
        if (count($columns) !== 1) {
            throw new InternalServerErrorException('Provided table has multiple primary key columns which are not supported');
        }

        $primaryKey = $columns[0] ?? null;
        if (empty($primaryKey)) {
            throw new InternalServerErrorException('Provided table has no primary key');
        }

        return $primaryKey;
    }

    protected function getRow(DatabaseRow $payload, Table $table): array
    {
        $result = [];
        foreach ($table->getColumns() as $column) {
            if ($payload->containsKey($column->getName())) {
                $value = $payload->get($column->getName());
                if ($value instanceof LocalDate) {
                    $value = $value->toDateTime()->format('Y-m-d');
                } elseif ($value instanceof LocalDateTime) {
                    $value = $value->toDateTime()->format('Y-m-d H:i:s');
                } elseif ($value instanceof LocalTime) {
                    $value = $value->toDateTime()->format('H:i:s');
                }

                $result[$column->getName()] = $value;
            }
        }

        return $result;
    }

    protected function createTable(DatabaseTable $table): Table
    {
        $tableName = $table->getName() ?? throw new BadRequestException('Table name not set');
        $columns = $table->getColumns() ?? throw new BadRequestException('Provided no columns');

        $result = new Table($tableName);
        foreach ($columns as $column) {
            $columnName = $column->getName() ?? throw new BadRequestException('Column name not set');
            $columnType = $column->getType() ?? throw new BadRequestException('Column type not set');

            $newColumn = $result->addColumn($columnName, $columnType);

            $notNull = $column->getNotNull();
            if ($notNull !== null) {
                $newColumn->setNotnull($notNull);
            }

            $autoIncrement = $column->getAutoIncrement();
            if ($autoIncrement !== null) {
                $newColumn->setAutoincrement($autoIncrement);
            }

            $default = $column->getDefault();
            if ($default !== null) {
                $newColumn->setDefault($default);
            }

            $length = $column->getLength();
            if ($length !== null) {
                $newColumn->setLength($length);
            }

            $fixed = $column->getFixed();
            if ($fixed !== null) {
                $newColumn->setFixed($fixed);
            }

            $precision = $column->getPrecision();
            if ($precision !== null) {
                $newColumn->setPrecision($precision);
            }

            $scale = $column->getScale();
            if ($scale !== null) {
                $newColumn->setScale($scale);
            }
        }

        $primaryKey = $table->getPrimaryKey();
        if (!empty($primaryKey)) {
            $result->setPrimaryKey([$primaryKey]);
        } else {
            throw new BadRequestException('Primary key not set');
        }

        $indexes = $table->getIndexes() ?? [];
        foreach ($indexes as $index) {
            $indexName = $index->getName();
            if (empty($indexName)) {
                $indexName = null;
            }

            $columns = $index->getColumns() ?? throw new BadRequestException('Provided no columns');

            if ($index->getUnique()) {
                $result->addUniqueIndex($columns, $indexName);
            } else {
                $result->addIndex($columns, $indexName);
            }
        }

        $foreignKeys = $table->getForeignKeys() ?? [];
        foreach ($foreignKeys as $foreignKey) {
            $constraintName = $foreignKey->getName();
            if (empty($constraintName)) {
                $constraintName = null;
            }

            $foreignTable = $foreignKey->getForeignTable() ?? throw new BadRequestException('Provided no foreign table');
            $localColumnNames = $foreignKey->getLocalColumnNames() ?? throw new BadRequestException('Provided no local column names');
            $foreignColumnNames = $foreignKey->getForeignColumnNames() ?? throw new BadRequestException('Provided no foreign column names');

            $result->addForeignKeyConstraint($foreignTable, $localColumnNames, $foreignColumnNames, [], $constraintName);
        }

        return $result;
    }
}

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
use PSX\Http\Exception\BadRequestException;
use PSX\Http\Exception\InternalServerErrorException;
use PSX\Http\Exception\NotFoundException;
use PSX\Record\RecordInterface;

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

    protected function getRow(RecordInterface $input, Table $table): array
    {
        $result = [];
        foreach ($table->getColumns() as $column) {
            if ($input->containsKey($column->getName())) {
                $result[$column->getName()] = $input->get($column->getName());
            }
        }

        return $result;
    }
}

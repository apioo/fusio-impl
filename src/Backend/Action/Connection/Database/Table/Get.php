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

namespace Fusio\Impl\Backend\Action\Connection\Database\Table;

use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\Action\Connection\Database\TableAbstract;

/**
 * Get
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Get extends TableAbstract
{
    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $connection = $this->getConnection($request);
        $table = $this->getTable($request, $connection->createSchemaManager());

        return $this->serializeTable($table);
    }

    private function serializeTable(Table $table): array
    {
        $return = [
            'name' => $table->getName(),
            'columns' => $this->serializeColumns($table),
        ];

        $primaryKey = $table->getPrimaryKey();
        if ($primaryKey instanceof Index) {
            // we support only single primary keys
            $firstPrimaryKeyColumn = $primaryKey->getColumns()[0] ?? null;
            if (!empty($firstPrimaryKeyColumn)) {
                $return['primaryKey'] = $firstPrimaryKeyColumn;
            }
        }

        $return['indexes'] = $this->serializeIndexes($table);
        $return['foreignKeys'] = $this->serializeForeignKeys($table);

        return $return;
    }

    private function serializeColumns(Table $table): array
    {
        $result = [];
        foreach ($table->getColumns() as $column) {
            $result[] = [
                'name' => $column->getName(),
                'type' => Type::getTypeRegistry()->lookupName($column->getType()),
                'length' => $column->getLength(),
                'notNull' => $column->getNotnull(),
                'autoIncrement' => $column->getAutoincrement(),
                'precision' => $column->getPrecision(),
                'scale' => $column->getScale(),
                'unsigned' => $column->getUnsigned(),
                'fixed' => $column->getFixed(),
                'default' => $column->getDefault(),
                'comment' => $column->getComment(),
            ];
        }

        return $result;
    }

    private function serializeIndexes(Table $table): array
    {
        $result = [];

        foreach ($table->getIndexes() as $index) {
            if ($index->isPrimary()) {
                continue;
            }

            $result[] = [
                'name' => $index->getName(),
                'unique' => $index->isUnique(),
                'columns' => $index->getColumns(),
            ];
        }

        return $result;
    }

    private function serializeForeignKeys(Table $table): array
    {
        $result = [];

        foreach ($table->getForeignKeys() as $foreignKey) {
            $result[] = [
                'name' => $foreignKey->getName(),
                'foreignTable' => $foreignKey->getForeignTableName(),
                'localColumnNames' => $foreignKey->getLocalColumns(),
                'foreignColumnNames' => $foreignKey->getForeignColumns(),
            ];
        }

        return $result;
    }
}

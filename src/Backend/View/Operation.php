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

namespace Fusio\Impl\Backend\View;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Operation extends ViewAbstract
{
    public function getCollection(int $categoryId, int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        if ($sortBy === null) {
            $sortBy = Table\Generated\OperationTable::COLUMN_ID;
        }

        if ($sortOrder === null) {
            $sortOrder = OrderBy::DESC;
        }

        $condition  = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $categoryId ?: 1);
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, Table\Operation::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like(Table\Generated\OperationTable::COLUMN_HTTP_PATH, '%' . $search . '%');
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Operation::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Operation::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_STATUS),
                'active' => $builder->fieldBoolean(Table\Generated\OperationTable::COLUMN_ACTIVE),
                'public' => $builder->fieldBoolean(Table\Generated\OperationTable::COLUMN_PUBLIC),
                'stability' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_STABILITY),
                'httpMethod' => Table\Generated\OperationTable::COLUMN_HTTP_METHOD,
                'httpPath' => Table\Generated\OperationTable::COLUMN_HTTP_PATH,
                'httpCode' => Table\Generated\OperationTable::COLUMN_HTTP_CODE,
                'name' => Table\Generated\OperationTable::COLUMN_NAME,
                'action' => Table\Generated\OperationTable::COLUMN_ACTION,
                'metadata' => $builder->fieldJson(Table\Generated\OperationTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Operation::class), 'findOneByIdentifier'], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_STATUS),
            'name' => Table\Generated\OperationTable::COLUMN_NAME,
            'scopes' => $builder->doColumn([$this->getTable(Table\Scope\Operation::class), 'getScopeNamesForOperation'], [new Reference('id')], 'name'),
            'active' => $builder->fieldBoolean(Table\Generated\OperationTable::COLUMN_ACTIVE),
            'public' => $builder->fieldBoolean(Table\Generated\OperationTable::COLUMN_PUBLIC),
            'stability' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_STABILITY),
            'description' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_DESCRIPTION),
            'httpMethod' => Table\Generated\OperationTable::COLUMN_HTTP_METHOD,
            'httpPath' => Table\Generated\OperationTable::COLUMN_HTTP_PATH,
            'httpCode' => Table\Generated\OperationTable::COLUMN_HTTP_CODE,
            'parameters' => $builder->fieldJson(Table\Generated\OperationTable::COLUMN_PARAMETERS),
            'incoming' => Table\Generated\OperationTable::COLUMN_INCOMING,
            'outgoing' => Table\Generated\OperationTable::COLUMN_OUTGOING,
            'throws' => $builder->fieldJson(Table\Generated\OperationTable::COLUMN_THROWS),
            'action' => Table\Generated\OperationTable::COLUMN_ACTION,
            'costs' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_COSTS),
            'metadata' => $builder->fieldJson(Table\Generated\OperationTable::COLUMN_METADATA),
        ]);

        return $builder->build($definition);
    }

    public function getRoutes(int $categoryId)
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(['operation.http_method', 'operation.http_path', 'operation.name'])
            ->from('fusio_operation', 'operation')
            ->where('(operation.category_id = :category_id)')
            ->orderBy('operation.id', 'ASC')
            ->setParameter('category_id', $categoryId);

        $builder = new Builder($this->connection);

        $definition = [
            'routes' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'http_path' => 'http_path',
                'http_method' => 'http_method',
                'name' => 'name',
            ], null, function (array $result) {
                $data = [];

                foreach ($result as $row) {
                    if (!isset($data[$row['http_path']])) {
                        $data[$row['http_path']] = [];
                    }

                    $data[$row['http_path']][$row['http_method']] = $row['name'];
                }

                return $data;
            }),
        ];

        return $builder->build($definition);
    }
}

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

namespace Fusio\Impl\Backend\View;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Operation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
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
                'active' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_ACTIVE),
                'public' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_PUBLIC),
                'stability' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_STABILITY),
                'httpMethod' => Table\Generated\OperationTable::COLUMN_HTTP_METHOD,
                'httpPath' => Table\Generated\OperationTable::COLUMN_HTTP_PATH,
                'name' => Table\Generated\OperationTable::COLUMN_NAME,
                'action' => Table\Generated\OperationTable::COLUMN_ACTION,
                'metadata' => $builder->fieldJson(Table\Generated\OperationTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id)
    {
        if (str_starts_with($id, '~')) {
            $method = 'findOneByName';
            $id = urldecode(substr($id, 1));
        } else {
            $method = 'find';
            $id = (int) $id;
        }

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Operation::class), $method], [$id], [
            'id' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_STATUS),
            'active' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_ACTIVE),
            'public' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_PUBLIC),
            'stability' => $builder->fieldInteger(Table\Generated\OperationTable::COLUMN_STABILITY),
            'httpMethod' => Table\Generated\OperationTable::COLUMN_HTTP_METHOD,
            'httpPath' => Table\Generated\OperationTable::COLUMN_HTTP_PATH,
            'name' => Table\Generated\OperationTable::COLUMN_NAME,
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

    public function getPublic(?string $category)
    {
        if (!empty($category)) {
            $categoryId = (int) $this->connection->fetchOne('SELECT id FROM fusio_category WHERE name = :name', ['name' => $category]);
        } else {
            $categoryId = 1;
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select(['operation.http_method', 'operation.http_path', 'operation.name'])
            ->from('fusio_operation', 'operation')
            ->where('(operation.category_id = :category_id)')
            ->orderBy('operation.id', 'ASC')
            ->setParameter('category_id', $categoryId);

        $builder = new Builder($this->connection);

        $definition = [
            'routes' => $builder->doCollection($queryBuilder->getSQL(), $queryBuilder->getParameters(), [
                'path' => 'http_path',
                'method' => 'http_method',
                'operation' => 'name',
            ], null, function (array $result) {
                $data = [];

                foreach ($result as $row) {
                    if (!isset($data[$row['http_path']])) {
                        $data[$row['http_method']] = [];
                    }

                    $data[$row['http_path']][$row['http_method']] = $row['name'];
                }

                return $data;
            }),
        ];

        return $builder->build($definition);
    }
}

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
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
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

        $definition = [
            'totalResults' => $this->getTable(Table\Operation::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Operation::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $this->fieldInteger(Table\Generated\RoutesTable::COLUMN_ID),
                'status' => $this->fieldInteger(Table\Generated\RoutesTable::COLUMN_STATUS),
                'path' => Table\Generated\RoutesTable::COLUMN_PATH,
                'controller' => Table\Generated\RoutesTable::COLUMN_CONTROLLER,
                'metadata' => $this->fieldJson(Table\Generated\RoutesTable::COLUMN_METADATA),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity(string $id)
    {
        if (str_starts_with($id, '~')) {
            $method = 'findOneByPath';
            $id = urldecode(substr($id, 1));
        } else {
            $method = 'find';
            $id = (int) $id;
        }

        $definition = $this->doEntity([$this->getTable(Table\Operation::class), $method], [$id], [
            'id' => $this->fieldInteger(Table\Generated\RoutesTable::COLUMN_ID),
            'status' => $this->fieldInteger(Table\Generated\RoutesTable::COLUMN_STATUS),
            'path' => Table\Generated\RoutesTable::COLUMN_PATH,
            'controller' => Table\Generated\RoutesTable::COLUMN_CONTROLLER,
            'metadata' => $this->fieldJson(Table\Generated\RoutesTable::COLUMN_METADATA),
            'scopes' => $this->doColumn([$this->getTable(Table\Scope\Operation::class), 'getScopeNamesForOperation'], [new Reference('id')], 'name'),
            'config' => $this->doCollection([$this->getTable(Table\Route\Method::class), 'getMethods'], [new Reference('id'), null, null], [
                'version' => Table\Generated\RoutesMethodTable::COLUMN_VERSION,
                'status' => Table\Generated\RoutesMethodTable::COLUMN_STATUS,
                'method' => Table\Generated\RoutesMethodTable::COLUMN_METHOD,
                'active' => Table\Generated\RoutesMethodTable::COLUMN_ACTIVE,
                'public' => Table\Generated\RoutesMethodTable::COLUMN_PUBLIC,
                'description' => Table\Generated\RoutesMethodTable::COLUMN_DESCRIPTION,
                'operationId' => Table\Generated\RoutesMethodTable::COLUMN_OPERATION_ID,
                'parameters' => Table\Generated\RoutesMethodTable::COLUMN_PARAMETERS,
                'request' => Table\Generated\RoutesMethodTable::COLUMN_REQUEST,
                'responses' => $this->doCollection([$this->getTable(Table\Route\Response::class), 'getResponses'], [new Reference('id')], [
                    'code' => Table\Generated\RoutesResponseTable::COLUMN_CODE,
                    'response' => Table\Generated\RoutesResponseTable::COLUMN_RESPONSE,
                ]),
                'action' => 'action',
                'costs' => 'costs',
            ], null, function (array $result) {
                $data = [];
                foreach ($result as $row) {
                    if (!isset($data[$row['version']])) {
                        $data[$row['version']] = [
                            'version' => (int) $row['version'],
                            'status'  => (int) $row['status'],
                            'methods' => new \stdClass(),
                        ];
                    }

                    $method = new \stdClass();
                    $method->active = (bool) $row['active'];
                    $method->public = (bool) $row['public'];

                    if (!empty($row['description'])) {
                        $method->description = $row['description'];
                    }

                    if (!empty($row['operationId'])) {
                        $method->operationId = $row['operationId'];
                    }

                    if (!empty($row['parameters'])) {
                        $method->parameters = $row['parameters'];
                    }

                    if (!empty($row['request'])) {
                        $method->request = $row['request'];
                    }

                    if (!empty($row['responses'])) {
                        $responses = [];
                        foreach ($row['responses'] as $response) {
                            $responses[$response['code']] = $response['response'];
                        }
                        $method->responses = $responses;
                    }

                    if (!empty($row['action'])) {
                        $method->action = $row['action'];
                    }

                    if (!empty($row['costs'])) {
                        $method->costs = (int) $row['costs'];
                    }

                    $data[$row['version']]['methods']->{$row['method']} = $method;
                }

                return array_values($data);
            }),
        ]);

        return $this->build($definition);
    }

    public function getPublic(?string $category)
    {
        if (!empty($category)) {
            $categoryId = (int) $this->connection->fetchOne('SELECT id FROM fusio_category WHERE name = :name', ['name' => $category]);
        } else {
            $categoryId = 1;
        }

        $builder = $this->connection->createQueryBuilder()
            ->select(['route.path', 'method.method', 'method.action'])
            ->from('fusio_routes', 'route')
            ->innerJoin('route', 'fusio_routes_method', 'method', 'route.id = method.route_id')
            ->where('(route.category_id = :category_id)')
            ->orderBy('route.id', 'ASC')
            ->setParameter('category_id', $categoryId);

        $definition = [
            'routes' => $this->doCollection($builder->getSQL(), $builder->getParameters(), [
                'path' => 'path',
                'method' => 'method',
                'action' => 'action',
            ], null, function (array $result) {
                $data = [];

                foreach ($result as $row) {
                    if (!isset($data[$row['path']])) {
                        $data[$row['path']] = [];
                    }

                    $data[$row['path']][$row['method']] = $row['action'];
                }

                return $data;
            }),
        ];

        return $this->build($definition);
    }
}

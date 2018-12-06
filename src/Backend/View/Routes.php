<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Routes
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Routes extends ViewAbstract
{
    public function getCollection($startIndex = null, $count = null, $search = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $condition  = new Condition();
        $condition->equals('status', Table\Routes::STATUS_ACTIVE);
        $condition->notLike('path', '/backend%');
        $condition->notLike('path', '/consumer%');
        $condition->notLike('path', '/doc%');
        $condition->notLike('path', '/authorization%');
        $condition->notLike('path', '/export%');

        if (!empty($search)) {
            $condition->like('path', '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Routes::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Routes::class), 'getAll'], [$startIndex, $count, 'priority', Sql::SORT_DESC, $condition], [
                'id' => 'id',
                'status' => 'status',
                'path' => 'path',
                'controller' => 'controller',
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Routes::class), 'get'], [$id], [
            'id' => 'id',
            'status' => 'status',
            'path' => 'path',
            'controller' => 'controller',
            'scopes' => $this->doColumn([$this->getTable(Table\Scope\Route::class), 'getScopeNamesForRoute'], [new Reference('id')], 'name'),
            'config' => $this->doCollection([$this->getTable(Table\Routes\Method::class), 'getMethods'], [new Reference('id')], [
                'version' => 'version',
                'status' => 'status',
                'method' => 'method',
                'active' => 'active',
                'public' => 'public',
                'description' => 'description',
                'parameters' => 'parameters',
                'request' => 'request',
                'responses' => $this->doCollection([$this->getTable(Table\Routes\Response::class), 'getResponses'], [new Reference('id')], [
                    'code' => 'code',
                    'response' => 'response',
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

                    if (!empty($row['parameters'])) {
                        $method->parameters = (int) $row['parameters'];
                    }

                    if (!empty($row['request'])) {
                        $method->request = (int) $row['request'];
                    }

                    if (!empty($row['responses'])) {
                        $responses = [];
                        foreach ($row['responses'] as $response) {
                            $responses[$response['code']] = (int) $response['response'];
                        }
                        $method->responses = $responses;
                    }

                    if (!empty($row['action'])) {
                        $method->action = (int) $row['action'];
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
}

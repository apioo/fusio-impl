<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Table;

use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\TableAbstract;

/**
 * Routes
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Routes extends TableAbstract
{
    const STATUS_ACTIVE  = 1;
    const STATUS_DELETED = 0;

    public function getName()
    {
        return 'fusio_routes';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'status' => self::TYPE_INT,
            'methods' => self::TYPE_VARCHAR,
            'path' => self::TYPE_VARCHAR,
            'controller' => self::TYPE_VARCHAR,
        );
    }

    public function getRoutes($startIndex = 0, $search = null)
    {
        $condition  = new Condition();
        $condition->equals('status', self::STATUS_ACTIVE);
        $condition->notLike('path', '/backend%');
        $condition->notLike('path', '/consumer%');
        $condition->notLike('path', '/doc%');
        $condition->notLike('path', '/authorization%');
        $condition->notLike('path', '/export%');

        if (!empty($search)) {
            $condition->like('path', '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => 16,
            'entry' => $this->doCollection([$this, 'getAll'], [$startIndex, 16, null, Sql::SORT_DESC, $condition], [
                'id' => 'id',
                'status' => 'status',
                'path' => 'path',
                'controller' => 'controller',
            ]),
        ];

        return $this->build($definition);
    }

    public function getRoute($id)
    {
        $definition = $this->doEntity([$this, 'get'], [$id], [
            'id' => 'id',
            'status' => 'status',
            'path' => 'path',
            'controller' => 'controller',
            'config' => $this->doCollection([$this->getTable('Fusio\Impl\Table\Routes\Method'), 'getMethods'], [new Reference('id')], [
                'version'  => 'version',
                'status'   => 'status',
                'method'   => 'method',
                'active'   => 'active',
                'public'   => 'public',
                'request'  => 'request',
                'response' => 'response',
                'action'   => 'action',
            ], null, function (array $result) {
                $data = [];
                foreach ($result as $row) {
                    if (!isset($data[$row['version']])) {
                        $data[$row['version']] = [
                            'version' => $row['version'],
                            'status'  => $row['status'],
                            'methods' => [],
                        ];
                    }

                    $data[$row['version']]['methods'][$row['method']] = [
                        'active'   => $row['active'],
                        'public'   => $row['public'],
                        'request'  => $row['request'],
                        'response' => $row['response'],
                        'action'   => $row['action'],
                    ];
                }

                return array_values($data);
            }),
        ]);

        return $this->build($definition);
    }
}

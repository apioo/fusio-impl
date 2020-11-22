<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\View\App;

use Fusio\Impl\Backend\View\App\Token\QueryFilter;
use Fusio\Impl\Table;
use PSX\Sql\Fields;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Token extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, QueryFilter $filter)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $condition = $filter->getCondition();

        $definition = [
            'totalResults' => $this->getTable(Table\App\Token::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\App\Token::class), 'getAll'], [$startIndex, $count, null, Sql::SORT_DESC, $condition, Fields::blacklist(['token'])], [
                'id' => $this->fieldInteger('id'),
                'appId' => $this->fieldInteger('app_id'),
                'userId' => $this->fieldInteger('user_id'),
                'status' => $this->fieldInteger('status'),
                'scope' => $this->fieldCsv('scope'),
                'ip' => 'ip',
                'date' => $this->fieldDateTime('date'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\App\Token::class), 'get'], [$id], [
            'id' => 'id',
            'app' => $this->doEntity([$this->getTable(Table\App::class), 'get'], [new Reference('app_id')], [
                'id' => 'id',
                'userId' => 'user_id',
                'status' => 'status',
                'name' => 'name',
            ]),
            'user' => $this->doEntity([$this->getTable(Table\User::class), 'get'], [new Reference('user_id')], [
                'id' => 'id',
                'status' => 'status',
                'name' => 'name',
            ]),
            'status' => 'status',
            'token' => 'token',
            'scope' => $this->fieldCsv('scope'),
            'ip' => 'ip',
            'expire' => $this->fieldDateTime('expire'),
            'date' => $this->fieldDateTime('date'),
        ]);

        return $this->build($definition);
    }
}

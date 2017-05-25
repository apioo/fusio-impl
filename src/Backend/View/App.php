<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use PSX\Sql\Fields;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * App
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class App extends ViewAbstract
{
    public function getCollection($startIndex = 0, $search = null)
    {
        $condition = new Condition();
        $condition->in('status', [Table\App::STATUS_ACTIVE, Table\App::STATUS_PENDING]);

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\App::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => 16,
            'entry' => $this->doCollection([$this->getTable(Table\App::class), 'getAll'], [$startIndex, 16, null, Sql::SORT_DESC, $condition, Fields::blacklist(['url', 'parameters', 'appSecret'])], [
                'id' => 'id',
                'userId' => 'userId',
                'status' => 'status',
                'name' => 'name',
                'appKey' => 'appKey',
                'date' => $this->fieldDateTime('date'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\App::class), 'get'], [$id], [
            'id' => 'id',
            'userId' => 'userId',
            'status' => 'status',
            'name' => 'name',
            'url' => 'url',
            'parameters' => 'parameters',
            'appKey' => 'appKey',
            'appSecret' => 'appSecret',
            'scopes' => $this->doColumn([$this->getTable(Table\App\Scope::class), 'getAvailableScopes'], [new Reference('id')], 'name'),
            'tokens' => $this->doCollection([$this->getTable(Table\App\Token::class), 'getTokensByApp'], [new Reference('id')], [
                'id' => 'id',
                'userId' => 'userId',
                'status' => 'status',
                'token' => 'token',
                'scope' => $this->fieldCsv('scope'),
                'ip' => 'ip',
                'expire' => 'expire',
                'date' => $this->fieldDateTime('date'),
            ]),
            'date' => $this->fieldDateTime('date'),
        ]);

        return $this->build($definition);
    }
}

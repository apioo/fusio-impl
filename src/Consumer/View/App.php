<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Consumer\View;

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
 * @link    https://www.fusio-project.org
 */
class App extends ViewAbstract
{
    public function getCollection(int $userId, int $startIndex = 0, ?string $search = null)
    {
        $condition = new Condition();
        $condition->equals('user_id', $userId);
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\App::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => 16,
            'entry' => $this->doCollection([$this->getTable(Table\App::class), 'getAll'], [$startIndex, 16, null, Sql::SORT_DESC, $condition, Fields::blacklist(['url', 'parameters', 'appSecret'])], [
                'id' => $this->fieldInteger('id'),
                'userId' => $this->fieldInteger('user_id'),
                'status' => $this->fieldInteger('status'),
                'name' => 'name',
                'appKey' => 'app_key',
                'date' => $this->fieldDateTime('date'),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($userId, $appId)
    {
        $condition = new Condition();
        $condition->equals('id', $appId);
        $condition->equals('user_id', $userId);
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        $definition = $this->doEntity([$this->getTable(Table\App::class), 'getOneBy'], [$condition], [
            'id' => $this->fieldInteger('id'),
            'userId' => $this->fieldInteger('user_id'),
            'status' => $this->fieldInteger('status'),
            'name' => 'name',
            'url' => 'url',
            'appKey' => 'app_key',
            'appSecret' => 'app_secret',
            'scopes' => $this->doColumn([$this->getTable(Table\App\Scope::class), 'getAvailableScopes'], [new Reference('id')], 'name'),
            'tokens' => $this->doCollection([$this->getTable(Table\App\Token::class), 'getTokensByApp'], [new Reference('id')], [
                'id' => $this->fieldInteger('id'),
                'userId' => $this->fieldInteger('user_id'),
                'status' => $this->fieldInteger('status'),
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

    public function getEntityByAppKey($appKey, $scope)
    {
        $condition = new Condition();
        $condition->equals('status', Table\App::STATUS_ACTIVE);
        $condition->equals('app_key', $appKey);

        $definition = $this->doEntity([$this->getTable(Table\App::class), 'getOneBy'], [$condition, Fields::blacklist(['user_id', 'status', 'parameters', 'app_key', 'app_secret', 'date'])], [
            'id' => $this->fieldInteger('id'),
            'name' => 'name',
            'url' => 'url',
            'scopes' => $this->doCollection([$this->getTable(Table\App\Scope::class), 'getValidScopes'], [new Reference('id'), explode(',', $scope), ['backend']], [
                'id' => $this->fieldInteger('id'),
                'name' => 'name',
                'description' => 'description',
            ]),
        ]);

        return $this->build($definition);
    }
}

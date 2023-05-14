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

namespace Fusio\Impl\Consumer\View;

use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Nested\Reference;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
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
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppTable::COLUMN_USER_ID, $userId);
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like(Table\Generated\AppTable::COLUMN_NAME, '%' . $search . '%');
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\App::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => 16,
            'entry' => $builder->doCollection([$this->getTable(Table\App::class), 'findAll'], [$condition, $startIndex, 16, null, OrderBy::DESC], [
                'id' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
                'userId' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_USER_ID),
                'status' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_STATUS),
                'name' => Table\Generated\AppTable::COLUMN_NAME,
                'appKey' => Table\Generated\AppTable::COLUMN_APP_KEY,
                'metadata' => $builder->fieldJson(Table\Generated\AppTable::COLUMN_METADATA),
                'date' => $builder->fieldDateTime(Table\Generated\AppTable::COLUMN_DATE),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(int $userId, int $appId)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppTable::COLUMN_ID, $appId);
        $condition->equals(Table\Generated\AppTable::COLUMN_USER_ID, $userId);
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\App::class), 'findOneBy'], [$condition], [
            'id' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
            'userId' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_USER_ID),
            'status' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_STATUS),
            'name' => Table\Generated\AppTable::COLUMN_NAME,
            'url' => Table\Generated\AppTable::COLUMN_URL,
            'appKey' => Table\Generated\AppTable::COLUMN_APP_KEY,
            'appSecret' => Table\Generated\AppTable::COLUMN_APP_SECRET,
            'metadata' => $builder->fieldJson(Table\Generated\AppTable::COLUMN_METADATA),
            'scopes' => $builder->doColumn([$this->getTable(Table\App\Scope::class), 'getAvailableScopes'], [new Reference('id')], 'name'),
            'tokens' => $builder->doCollection([$this->getTable(Table\App\Token::class), 'getTokensByApp'], [new Reference('id')], [
                'id' => $builder->fieldInteger(Table\Generated\AppTokenTable::COLUMN_ID),
                'userId' => $builder->fieldInteger(Table\Generated\AppTokenTable::COLUMN_USER_ID),
                'status' => $builder->fieldInteger(Table\Generated\AppTokenTable::COLUMN_STATUS),
                'token' => Table\Generated\AppTokenTable::COLUMN_TOKEN,
                'scope' => $builder->fieldCsv(Table\Generated\AppTokenTable::COLUMN_SCOPE),
                'ip' => Table\Generated\AppTokenTable::COLUMN_IP,
                'expire' => Table\Generated\AppTokenTable::COLUMN_EXPIRE,
                'date' => $builder->fieldDateTime(Table\Generated\AppTokenTable::COLUMN_DATE),
            ]),
            'date' => $builder->fieldDateTime(Table\Generated\AppTable::COLUMN_DATE),
        ]);

        return $builder->build($definition);
    }

    public function getEntityByAppKey($appKey, $scope)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);
        $condition->equals(Table\Generated\AppTable::COLUMN_APP_KEY, $appKey);

        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\App::class), 'findOneBy'], [$condition], [
            'id' => $builder->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
            'name' => Table\Generated\AppTable::COLUMN_NAME,
            'url' => Table\Generated\AppTable::COLUMN_URL,
            'scopes' => $builder->doCollection([$this->getTable(Table\App\Scope::class), 'getValidScopes'], [new Reference('id'), explode(',', $scope), ['backend']], [
                'id' => $builder->fieldInteger(Table\Generated\ScopeTable::COLUMN_ID),
                'name' => Table\Generated\ScopeTable::COLUMN_NAME,
                'description' => Table\Generated\ScopeTable::COLUMN_DESCRIPTION,
            ]),
        ]);

        return $builder->build($definition);
    }
}

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
 * @link    https://www.fusio-project.org
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
            'entry' => $this->doCollection([$this->getTable(Table\App\Token::class), 'findAll'], [$condition, $startIndex, $count, null, Sql::SORT_DESC], [
                'id' => $this->fieldInteger(Table\Generated\AppTokenTable::COLUMN_ID),
                'appId' => $this->fieldInteger(Table\Generated\AppTokenTable::COLUMN_APP_ID),
                'userId' => $this->fieldInteger(Table\Generated\AppTokenTable::COLUMN_USER_ID),
                'status' => $this->fieldInteger(Table\Generated\AppTokenTable::COLUMN_STATUS),
                'scope' => $this->fieldCsv(Table\Generated\AppTokenTable::COLUMN_SCOPE),
                'ip' => Table\Generated\AppTokenTable::COLUMN_IP,
                'date' => $this->fieldDateTime(Table\Generated\AppTokenTable::COLUMN_DATE),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\App\Token::class), 'find'], [$id], [
            'id' => Table\Generated\AppTokenTable::COLUMN_ID,
            'app' => $this->doEntity([$this->getTable(Table\App::class), 'find'], [new Reference('app_id')], [
                'id' => Table\Generated\AppTable::COLUMN_ID,
                'userId' => Table\Generated\AppTable::COLUMN_USER_ID,
                'status' => Table\Generated\AppTable::COLUMN_STATUS,
                'name' => Table\Generated\AppTable::COLUMN_NAME,
            ]),
            'user' => $this->doEntity([$this->getTable(Table\User::class), 'find'], [new Reference('user_id')], [
                'id' => Table\Generated\UserTable::COLUMN_ID,
                'status' => Table\Generated\UserTable::COLUMN_STATUS,
                'name' => Table\Generated\UserTable::COLUMN_NAME,
            ]),
            'status' => Table\Generated\AppTokenTable::COLUMN_STATUS,
            'token' => Table\Generated\AppTokenTable::COLUMN_TOKEN,
            'scope' => $this->fieldCsv(Table\Generated\AppTokenTable::COLUMN_SCOPE),
            'ip' => Table\Generated\AppTokenTable::COLUMN_IP,
            'expire' => $this->fieldDateTime(Table\Generated\AppTokenTable::COLUMN_EXPIRE),
            'date' => $this->fieldDateTime(Table\Generated\AppTokenTable::COLUMN_DATE),
        ]);

        return $this->build($definition);
    }
}

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
    public function getCollection(int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        if ($sortBy === null) {
            $sortBy = Table\Generated\AppTable::COLUMN_ID;
        }

        if ($sortOrder === null) {
            $sortOrder = Sql::SORT_DESC;
        }

        $condition = new Condition();
        $condition->in(Table\Generated\AppTable::COLUMN_STATUS, [Table\App::STATUS_ACTIVE, Table\App::STATUS_PENDING]);

        if (!empty($search)) {
            $condition->like(Table\Generated\AppTable::COLUMN_NAME, '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\App::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\App::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $this->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
                'userId' => $this->fieldInteger(Table\Generated\AppTable::COLUMN_USER_ID),
                'status' => $this->fieldInteger(Table\Generated\AppTable::COLUMN_STATUS),
                'name' => Table\Generated\AppTable::COLUMN_NAME,
                'appKey' => Table\Generated\AppTable::COLUMN_APP_KEY,
                'date' => $this->fieldDateTime(Table\Generated\AppTable::COLUMN_DATE),
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity(int $id)
    {
        $definition = $this->doEntity([$this->getTable(Table\App::class), 'find'], [$id], [
            'id' => $this->fieldInteger(Table\Generated\AppTable::COLUMN_ID),
            'userId' => $this->fieldInteger(Table\Generated\AppTable::COLUMN_USER_ID),
            'status' => $this->fieldInteger(Table\Generated\AppTable::COLUMN_STATUS),
            'name' => Table\Generated\AppTable::COLUMN_NAME,
            'url' => Table\Generated\AppTable::COLUMN_URL,
            'parameters' => Table\Generated\AppTable::COLUMN_PARAMETERS,
            'appKey' => Table\Generated\AppTable::COLUMN_APP_KEY,
            'appSecret' => Table\Generated\AppTable::COLUMN_APP_SECRET,
            'scopes' => $this->doColumn([$this->getTable(Table\App\Scope::class), 'getAvailableScopes'], [new Reference('id')], 'name'),
            'tokens' => $this->doCollection([$this->getTable(Table\App\Token::class), 'getTokensByApp'], [new Reference('id')], [
                'id' => $this->fieldInteger(Table\Generated\AppTokenTable::COLUMN_ID),
                'userId' => $this->fieldInteger(Table\Generated\AppTokenTable::COLUMN_USER_ID),
                'status' => $this->fieldInteger(Table\Generated\AppTokenTable::COLUMN_STATUS),
                'token' => Table\Generated\AppTokenTable::COLUMN_TOKEN,
                'scope' => $this->fieldCsv(Table\Generated\AppTokenTable::COLUMN_SCOPE),
                'ip' => Table\Generated\AppTokenTable::COLUMN_IP,
                'expire' => Table\Generated\AppTokenTable::COLUMN_EXPIRE,
                'date' => $this->fieldDateTime(Table\Generated\AppTokenTable::COLUMN_DATE),
            ]),
            'metadata' => $this->fieldJson(Table\Generated\AppTable::COLUMN_METADATA),
            'date' => $this->fieldDateTime(Table\Generated\AppTable::COLUMN_DATE),
        ]);

        return $this->build($definition);
    }
}

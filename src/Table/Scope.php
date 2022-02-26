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

namespace Fusio\Impl\Table;

use PSX\Sql\Condition;
use PSX\Sql\Sql;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Scope extends Generated\ScopeTable
{
    public const STATUS_ACTIVE  = 1;
    public const STATUS_DELETED = 0;

    public function getValidScopes(array $names)
    {
        $names = array_filter($names);

        if (!empty($names)) {
            return $this->findAll(new Condition(['name', 'IN', $names]), 0, 1024);
        } else {
            return [];
        }
    }

    public function getAvailableScopes(int $categoryId)
    {
        $condition = new Condition(['category_id', '=', $categoryId]);

        $result = $this->findAll($condition, 0, 1024, 'name', Sql::SORT_ASC);
        $scopes = [];
        foreach ($result as $row) {
            $scopes[$row['name']] = $row['description'];
        }

        return $scopes;
    }

    public static function getNames(array $result)
    {
        return array_map(function ($row) {
            return $row['name'];
        }, $result);
    }
}

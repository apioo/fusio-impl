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

namespace Fusio\Impl\Table;

use PSX\Sql\Condition;
use PSX\Sql\TableAbstract;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Scope extends TableAbstract
{
    const TYPE_BACKEND = 'backend';
    const TYPE_CONSUMER = 'consumer';
    const TYPE_APP = 'app';

    public function getName()
    {
        return 'fusio_scope';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'name' => self::TYPE_VARCHAR,
            'description' => self::TYPE_VARCHAR,
        );
    }

    public function getValidScopes(array $names)
    {
        $names = array_filter($names);

        if (!empty($names)) {
            return $this->getAll(0, 1024, null, null, new Condition(['name', 'IN', $names]));
        } else {
            return [];
        }
    }

    public function getScopesForType(string $type)
    {
        if ($type === self::TYPE_BACKEND) {
            $condition = new Condition();
            $condition->like('name', 'backend%');
            $result = $this->getAll(0, 1024, null, null, $condition);
        } elseif ($type === self::TYPE_CONSUMER) {
            $condition = new Condition();
            $condition->like('name', 'consumer%');
            $result = $this->getAll(0, 1024, null, null, $condition);
        } else {
            $condition = new Condition();
            $condition->notLike('name', 'backend%');
            $condition->notLike('name', 'consumer%');
            $result = $this->getAll(0, 1024, null, null, $condition);
        }

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

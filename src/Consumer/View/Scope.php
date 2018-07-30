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

namespace Fusio\Impl\Consumer\View;

use PSX\Sql\ViewAbstract;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Scope extends ViewAbstract
{
    public function getCollection($userId, $startIndex = 0)
    {
        $sql = '    SELECT scope.id,
                           scope.name,
                           scope.description
                      FROM fusio_user_scope user_scope
                INNER JOIN fusio_scope scope
                        ON user_scope.scope_id = scope.id
                     WHERE user_scope.user_id = :user_id
                  ORDER BY scope.id ASC';

        $definition = [
            'entry' => $this->doCollection($sql, ['user_id' => $userId], [
                'id' => 'id',
                'name' => 'name',
                'description' => 'description',
            ]),
        ];

        return $this->build($definition);
    }
}

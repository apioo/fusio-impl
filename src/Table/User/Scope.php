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

namespace Fusio\Impl\Table\User;

use PSX\Sql\Condition;
use PSX\Sql\Reference;
use PSX\Sql\Sql;
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
    public function getName()
    {
        return 'fusio_user_scope';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'userId' => self::TYPE_INT,
            'scopeId' => self::TYPE_INT,
        );
    }

    public function deleteAllFromUser($userId)
    {
        $sql = 'DELETE FROM fusio_user_scope
                      WHERE userId = :id';

        $this->connection->executeQuery($sql, array('id' => $userId));
    }

    public function getValidScopes($userId, array $scopes, array $exclude = array())
    {
        $result = $this->getAvailableScopes($userId);
        $data   = array();

        foreach ($result as $scope) {
            if (in_array($scope['name'], $scopes)) {
                // is the scope excluded
                if (in_array($scope['name'], $exclude)) {
                    continue;
                }

                $data[] = $scope;
            }
        }

        return $data;
    }

    public function getAvailableScopes($userId)
    {
        $sql = '    SELECT scope.id,
                           scope.name,
                           scope.description
                      FROM fusio_user_scope userScope
                INNER JOIN fusio_scope scope
                        ON scope.id = userScope.scopeId
                     WHERE userScope.userId = :userId
                  ORDER BY scope.id ASC';

        return $this->connection->fetchAll($sql, array('userId' => $userId)) ?: [];
    }
}

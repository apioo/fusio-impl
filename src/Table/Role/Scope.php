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

namespace Fusio\Impl\Table\Role;

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
        return 'fusio_role_scope';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'role_id' => self::TYPE_INT,
            'scope_id' => self::TYPE_INT,
        );
    }

    public function deleteAllFromRole($roleId)
    {
        $sql = 'DELETE FROM fusio_role_scope
                      WHERE role_id = :id';

        $this->connection->executeQuery($sql, array('id' => $roleId));
    }

    public function getValidScopes($roleId, array $scopes)
    {
        $result = $this->getAvailableScopes($roleId);
        $data   = array();

        foreach ($result as $scope) {
            if (in_array($scope['name'], $scopes)) {
                $data[] = $scope;
            }
        }

        return $data;
    }

    public function getAvailableScopes($roleId)
    {
        $sql = '    SELECT scope.id,
                           scope.name,
                           scope.description
                      FROM fusio_role_scope role_scope
                INNER JOIN fusio_scope scope
                        ON scope.id = role_scope.scope_id
                     WHERE role_scope.role_id = :role_id
                  ORDER BY scope.id ASC';
        $assignedScopes = $this->connection->fetchAll($sql, array('role_id' => $roleId)) ?: [];

        $scopes = [];
        foreach ($assignedScopes as $assignedScope) {
            $scopes[$assignedScope['name']] = $assignedScope;

            if (strpos($assignedScope['name'], '.') === false) {
                // load all sub scopes
                $sql = 'SELECT scope.id,
                               scope.name,
                               scope.description
                          FROM fusio_scope scope
                         WHERE scope.name LIKE :name';
                $subScopes = $this->connection->fetchAll($sql, ['name' => $assignedScope['name'] . '.%']);
                foreach ($subScopes as $subScope) {
                    $scopes[$subScope['name']] = $subScope;
                }
            }
        }

        return array_values($scopes);
    }
}

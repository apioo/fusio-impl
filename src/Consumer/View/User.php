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

namespace Fusio\Impl\Consumer\View;

use Fusio\Impl\Table;
use PSX\Sql\Reference;
use PSX\Sql\ViewAbstract;

/**
 * User
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class User extends ViewAbstract
{
    public function getEntity(int $id, array $userAttributes = null)
    {
        $definition = $this->doEntity([$this->getTable(Table\User::class), 'get'], [$id], [
            'id' => $this->fieldInteger('id'),
            'roleId' => $this->fieldInteger('role_id'),
            'status' => $this->fieldInteger('status'),
            'name' => 'name',
            'email' => 'email',
            'points' => $this->fieldInteger('points'),
            'scopes' => $this->doColumn([$this->getTable(Table\User\Scope::class), 'getAvailableScopes'], [new Reference('id')], 'name'),
            'attributes' => $this->doCollection([$this->getTable(Table\User\Attribute::class), 'getByUser_id'], [new Reference('id')], [
                'name' => 'name',
                'value' => 'value',
            ], null, function(array $result) use ($userAttributes){
                $values = [];
                foreach ($result as $row) {
                    $values[$row['name']] = $row['value'];
                }

                $data = [];
                if (!empty($userAttributes)) {
                    foreach ($userAttributes as $name) {
                        $data[$name] = $values[$name] ?? null;
                    }
                }

                return $data ?: null;
            }),
            'date' => $this->fieldDateTime('date'),
        ]);

        return $this->build($definition);
    }
}

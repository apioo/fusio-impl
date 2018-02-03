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

use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\Reference;
use PSX\Sql\ViewAbstract;

/**
 * App
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class App extends ViewAbstract
{
    public function getEntityByAppKey($appKey, $scope)
    {
        $condition = new Condition();
        $condition->equals('status', Table\App::STATUS_ACTIVE);
        $condition->equals('appKey', $appKey);

        $definition = $this->doEntity([$this->getTable(Table\App::class), 'getOneBy'], [$condition, Fields::blacklist(['userId', 'status', 'parameters', 'appKey', 'appSecret', 'date'])], [
            'id' => 'id',
            'name' => 'name',
            'url' => 'url',
            'scopes' => $this->doCollection([$this->getTable(Table\App\Scope::class), 'getValidScopes'], [new Reference('id'), explode(',', $scope), ['backend']], [
                'id' => 'id',
                'name' => 'name',
                'description' => 'description',
            ]),
        ]);

        return $this->build($definition);
    }
}

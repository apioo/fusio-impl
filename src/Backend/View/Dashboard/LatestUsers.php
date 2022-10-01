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

namespace Fusio\Impl\Backend\View\Dashboard;

use Fusio\Impl\Table;
use PSX\Sql\ViewAbstract;

/**
 * LatestUsers
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class LatestUsers extends ViewAbstract
{
    public function getView()
    {
        $sql = '  SELECT usr.id,
                         usr.status,
                         usr.name,
                         usr.date
                    FROM fusio_user usr
                   WHERE usr.status = :status 
                ORDER BY usr.id DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $definition = [
            'entry' => $this->doCollection($sql, ['status' => Table\User::STATUS_ACTIVE], [
                'id' => $this->fieldInteger('id'),
                'status' => $this->fieldInteger('status'),
                'name' => 'name',
                'date' => $this->fieldDateTime('date'),
            ]),
        ];

        return $this->build($definition);
    }
}

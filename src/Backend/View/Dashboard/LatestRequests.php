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

namespace Fusio\Impl\Backend\View\Dashboard;

use PSX\Sql\ViewAbstract;

/**
 * LatestRequests
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class LatestRequests extends ViewAbstract
{
    public function getView()
    {
        $sql = '  SELECT log.path,
                         log.ip,
                         log.date
                    FROM fusio_log log
                ORDER BY log.date DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);

        $definition = [
            'entry' => $this->doCollection($sql, [], [
                'path' => 'path',
                'ip' => 'ip',
                'date' => $this->fieldDateTime('date'),
            ]),
        ];

        return $this->build($definition);
    }
}

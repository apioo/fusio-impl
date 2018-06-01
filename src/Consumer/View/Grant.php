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
use PSX\Sql\ViewAbstract;

/**
 * Grant
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Grant extends ViewAbstract
{
    public function getCollection($userId)
    {
        $sql = '    SELECT userGrant.id,
                           userGrant.date,
                           userGrant.appId AS appId,
                           app.name AS appName,
                           app.url AS appUrl
                      FROM fusio_user_grant userGrant
                INNER JOIN fusio_app app
                        ON userGrant.appId = app.id
                     WHERE userGrant.allow = 1
                       AND userGrant.userId = :userId
                       AND app.status = :status';

        $definition = [
            'entry' => $this->doCollection($sql, ['userId' => $userId, 'status' => Table\App::STATUS_ACTIVE], [
                'id' => 'id',
                'createDate' => $this->fieldDateTime('date'),
                'app' => [
                    'id' => 'appId',
                    'name' => 'appName',
                    'url' => 'appUrl',
                ],
            ]),
        ];

        return $this->build($definition);
    }
}

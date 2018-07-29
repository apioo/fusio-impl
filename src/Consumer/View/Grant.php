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
        $sql = '    SELECT user_grant.id,
                           user_grant.date,
                           user_grant.app_id,
                           app.name AS app_name,
                           app.url AS app_url
                      FROM fusio_user_grant user_grant
                INNER JOIN fusio_app app
                        ON user_grant.app_id = app.id
                     WHERE user_grant.allow = 1
                       AND user_grant.user_id = :user_id
                       AND app.status = :status';

        $definition = [
            'entry' => $this->doCollection($sql, ['user_id' => $userId, 'status' => Table\App::STATUS_ACTIVE], [
                'id' => 'id',
                'createDate' => $this->fieldDateTime('date'),
                'app' => [
                    'id' => 'app_id',
                    'name' => 'app_name',
                    'url' => 'app_url',
                ],
            ]),
        ];

        return $this->build($definition);
    }
}

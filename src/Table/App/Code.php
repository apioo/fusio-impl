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

namespace Fusio\Impl\Table\App;

use Fusio\Impl\Table\App;
use PSX\Sql\TableAbstract;

/**
 * Code
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Code extends TableAbstract
{
    public function getName()
    {
        return 'fusio_app_code';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'app_id' => self::TYPE_INT,
            'user_id' => self::TYPE_INT,
            'code' => self::TYPE_VARCHAR,
            'redirect_uri' => self::TYPE_VARCHAR,
            'scope' => self::TYPE_VARCHAR,
            'state' => self::TYPE_VARCHAR,
            'date' => self::TYPE_DATETIME,
        );
    }

    public function getCodeByRequest($appKey, $appSecret, $code, $redirectUri)
    {
        $sql = '    SELECT code.id,
                           code.app_id,
                           code.user_id,
                           code.scope,
                           code.date
                      FROM fusio_app_code code
                INNER JOIN fusio_app app
                        ON app.id = code.app_id
                     WHERE app.app_key = :app_key
                       AND app.app_secret = :app_secret
                       AND app.status = :status
                       AND code.code = :code
                       AND code.redirect_uri = :redirect_uri';

        return $this->connection->fetchAssoc($sql, array(
            'app_key'      => $appKey,
            'app_secret'   => $appSecret,
            'status'       => App::STATUS_ACTIVE,
            'code'         => $code,
            'redirect_uri' => $redirectUri ?: '',
        ));
    }
}

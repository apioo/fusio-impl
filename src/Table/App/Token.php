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

namespace Fusio\Impl\Table\App;

use DateTime;
use Fusio\Impl\Table\Generated;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Token extends Generated\AppTokenTable
{
    const STATUS_ACTIVE  = 0x1;
    const STATUS_DELETED = 0x2;

    public function getTokensByApp($appId)
    {
        $now = new DateTime();
        $con = new Condition();
        $con->add('app_id', '=', $appId);
        $con->add('status', '=', self::STATUS_ACTIVE);
        $con->add('expire', '>', $now->format('Y-m-d H:i:s'));

        return $this->findBy($con);
    }

    public function getTokenByRefreshToken($appId, $refreshToken)
    {
        $con = new Condition();
        $con->add('app_id', '=', $appId);
        $con->add('refresh', '=', $refreshToken);

        return $this->findOneBy($con);
    }

    public function getTokenByToken($appId, $token)
    {
        $now = new DateTime();
        $con = new Condition();
        $con->add('app_id', '=', $appId);
        $con->add('status', '=', self::STATUS_ACTIVE);
        $con->add('expire', '>', $now->format('Y-m-d H:i:s'));
        $con->add('token', '=', $token);

        return $this->findOneBy($con);
    }

    public function removeTokenFromApp($appId, $tokenId)
    {
        $sql = 'UPDATE fusio_app_token
                   SET status = :status
                 WHERE app_id = :app_id
                   AND id = :id';

        $affectedRows = $this->connection->executeUpdate($sql, array(
            'status' => self::STATUS_DELETED,
            'app_id' => $appId,
            'id'     => $tokenId
        ));

        if ($affectedRows == 0) {
            throw new StatusCode\NotFoundException('Invalid token');
        }
    }

    public function removeAllTokensFromAppAndUser($appId, $userId)
    {
        $sql = 'UPDATE fusio_app_token
                   SET status = :status
                 WHERE app_id = :app_id
                   AND user_id = :user_id';

        $this->connection->executeUpdate($sql, array(
            'status'  => self::STATUS_DELETED,
            'app_id'  => $appId,
            'user_id' => $userId
        ));
    }
}

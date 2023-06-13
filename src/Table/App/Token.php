<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Token extends Generated\AppTokenTable
{
    const STATUS_ACTIVE  = 0x1;
    const STATUS_DELETED = 0x2;

    public function getTokensByApp(int $appId): array
    {
        $now = new DateTime();
        $con = Condition::withAnd();
        $con->equals('app_id', $appId);
        $con->equals('status', self::STATUS_ACTIVE);
        $con->greater('expire', $now->format('Y-m-d H:i:s'));

        return $this->findBy($con);
    }

    public function getTokenByRefreshToken(int $appId, string $refreshToken): ?Generated\AppTokenRow
    {
        $con = Condition::withAnd();
        $con->equals('app_id', $appId);
        $con->equals('refresh', $refreshToken);

        return $this->findOneBy($con);
    }

    public function getTokenByToken(int $appId, string $token): ?Generated\AppTokenRow
    {
        $now = new DateTime();
        $con = Condition::withAnd();
        $con->equals('app_id', $appId);
        $con->equals('status', self::STATUS_ACTIVE);
        $con->greater('expire', $now->format('Y-m-d H:i:s'));
        $con->equals('token', $token);

        return $this->findOneBy($con);
    }

    public function removeTokenFromApp(int $appId, int $tokenId): void
    {
        $sql = 'UPDATE fusio_app_token
                   SET status = :status
                 WHERE app_id = :app_id
                   AND id = :id';

        $affectedRows = $this->connection->executeStatement($sql, array(
            'status' => self::STATUS_DELETED,
            'app_id' => $appId,
            'id'     => $tokenId
        ));

        if ($affectedRows == 0) {
            throw new StatusCode\NotFoundException('Invalid token');
        }
    }

    public function removeAllTokensFromAppAndUser(int $appId, int $userId): void
    {
        $sql = 'UPDATE fusio_app_token
                   SET status = :status
                 WHERE app_id = :app_id
                   AND user_id = :user_id';

        $this->connection->executeStatement($sql, array(
            'status'  => self::STATUS_DELETED,
            'app_id'  => $appId,
            'user_id' => $userId
        ));
    }
}

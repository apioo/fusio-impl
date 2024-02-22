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

namespace Fusio\Impl\Table;

use DateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Token extends Generated\TokenTable
{
    const STATUS_ACTIVE  = 0x1;
    const STATUS_DELETED = 0x2;

    public function findByAccessToken(?string $tenantId, string $token): array|false
    {
        $now = new \DateTime();

        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);
        $condition->equals(self::COLUMN_TOKEN, $token);
        $condition->add(Condition::withOr()
            ->nil(self::COLUMN_EXPIRE)
            ->greater(self::COLUMN_EXPIRE, $now->format($this->connection->getDatabasePlatform()->getDateTimeFormatString()))
        );

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'token.' . self::COLUMN_ID,
                'token.' . self::COLUMN_APP_ID,
                'token.' . self::COLUMN_USER_ID,
                'token.' . self::COLUMN_TOKEN,
                'token.' . self::COLUMN_SCOPE,
                'token.' . self::COLUMN_EXPIRE,
                'token.' . self::COLUMN_DATE,
            ])
            ->from('fusio_token', 'token')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        return $this->connection->fetchAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    public function getTokensByApp(?string $tenantId, int $appId): array
    {
        $con = Condition::withAnd();
        $con->equals(self::COLUMN_TENANT_ID, $tenantId);
        $con->equals(self::COLUMN_APP_ID, $appId);
        $con->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);
        $con->greater(self::COLUMN_EXPIRE, (new DateTime())->format('Y-m-d H:i:s'));

        return $this->findBy($con);
    }

    public function findOneByTenantAndRefreshToken(?string $tenantId, string $refreshToken): ?Generated\TokenRow
    {
        $con = Condition::withAnd();
        $con->equals(self::COLUMN_TENANT_ID, $tenantId);
        $con->equals(self::COLUMN_REFRESH, $refreshToken);

        return $this->findOneBy($con);
    }

    public function getTokenByToken(?string $tenantId, int $appId, string $token): ?Generated\TokenRow
    {
        $con = Condition::withAnd();
        $con->equals(self::COLUMN_TENANT_ID, $tenantId);
        $con->equals(self::COLUMN_APP_ID, $appId);
        $con->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);
        $con->greater(self::COLUMN_EXPIRE, (new DateTime())->format('Y-m-d H:i:s'));
        $con->equals(self::COLUMN_TOKEN, $token);

        return $this->findOneBy($con);
    }

    public function removeTokenFromApp(?string $tenantId, int $appId, int $tokenId): void
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_APP_ID, $appId);
        $condition->equals(self::COLUMN_ID, $tokenId);

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->update('fusio_token', 'token');
        $queryBuilder->set('token.' . self::COLUMN_STATUS, '?');
        $queryBuilder->where($condition->getExpression($this->connection->getDatabasePlatform()));
        $queryBuilder->setParameters(array_merge([self::STATUS_DELETED], $condition->getValues()));

        $affectedRows = $this->connection->executeStatement($queryBuilder->getSQL(), $queryBuilder->getParameters());

        if ($affectedRows == 0) {
            throw new StatusCode\NotFoundException('Invalid token');
        }
    }

    public function removeAllTokensFromAppAndUser(?string $tenantId, int $appId, int $userId): void
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_APP_ID, $appId);
        $condition->equals(self::COLUMN_USER_ID, $userId);

        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->update('fusio_token', 'token');
        $queryBuilder->set('token.' . self::COLUMN_STATUS, '?');
        $queryBuilder->where($condition->getExpression($this->connection->getDatabasePlatform()));
        $queryBuilder->setParameters(array_merge([self::STATUS_DELETED], $condition->getValues()));

        $this->connection->executeStatement($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }
}

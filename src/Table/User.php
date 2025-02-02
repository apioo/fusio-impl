<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Table\Generated\ScopeRow;
use Fusio\Impl\Table\Generated\UserRow;
use PSX\Sql\Condition;

/**
 * User
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class User extends Generated\UserTable
{
    public const STATUS_DISABLED = 2;
    public const STATUS_ACTIVE   = 1;
    public const STATUS_DELETED  = 0;

    public function findOneByIdentifier(?string $tenantId, string $id): ?UserRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);

        if (str_starts_with($id, '~')) {
            $condition->equals(self::COLUMN_NAME, urldecode(substr($id, 1)));
        } else {
            $condition->equals(self::COLUMN_ID, (int) $id);
        }

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndId(?string $tenantId, int $id): ?UserRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndName(?string $tenantId, string $name): ?UserRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_NAME, $name);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndEmail(?string $tenantId, string $email): ?UserRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_EMAIL, $email);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndToken(?string $tenantId, string $token): ?UserRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_TOKEN, $token);

        return $this->findOneBy($condition);
    }

    public function findRemoteUser(?string $tenantId, int $identityId, string $remoteId): ?UserRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_IDENTITY_ID, $identityId);
        $condition->equals(self::COLUMN_REMOTE_ID, $remoteId);

        return $this->findOneBy($condition);
    }

    public function changePassword(?string $tenantId, int $userId, ?string $oldPassword, string $newPassword, bool $verifyOld = true): bool
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_ID, $userId);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'usr.' . self::COLUMN_PASSWORD,
            ])
            ->from('fusio_user', 'usr')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $password = $this->connection->fetchOne($queryBuilder->getSQL(), $queryBuilder->getParameters());
        if (empty($password)) {
            return false;
        }

        if (!$verifyOld || password_verify($oldPassword ?? '', $password)) {
            $this->connection->update('fusio_user', [
                self::COLUMN_PASSWORD => \password_hash($newPassword, PASSWORD_DEFAULT),
            ], [
                self::COLUMN_ID => $userId,
            ]);

            return true;
        } else {
            return false;
        }
    }

    public function payPoints(int $userId, int $points): void
    {
        $this->connection->executeStatement('UPDATE fusio_user SET points = COALESCE(points, 0) - :points WHERE id = :id', [
            'id' => $userId,
            'points' => $points,
        ]);
    }

    public function creditPoints(int $userId, int $points): void
    {
        $this->connection->executeStatement('UPDATE fusio_user SET points = COALESCE(points, 0) + :points WHERE id = :id', [
            'id' => $userId,
            'points' => $points,
        ]);
    }

    public function getPlanId(?string $tenantId, int $userId): int
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_ID, $userId);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'usr.' . self::COLUMN_PLAN_ID,
            ])
            ->from('fusio_user', 'usr')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        return (int) $this->connection->fetchOne($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }
}

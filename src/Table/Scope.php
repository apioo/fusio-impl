<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

use Fusio\Impl\Table\Generated\SchemaRow;
use Fusio\Impl\Table\Generated\ScopeColumn;
use Fusio\Impl\Table\Generated\ScopeRow;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * Scope
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Scope extends Generated\ScopeTable
{
    public const STATUS_ACTIVE  = 1;
    public const STATUS_DELETED = 0;

    public function findOneByIdentifier(?string $tenantId, int $categoryId, string $id): ?ScopeRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByTenantAndName($tenantId, $categoryId, urldecode(substr($id, 1)));
        } else {
            return $this->findOneByTenantAndId($tenantId, $categoryId, (int) $id);
        }
    }

    public function findOneByTenantAndId(?string $tenantId, int $categoryId, int $id): ?ScopeRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_CATEGORY_ID, $categoryId);
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndName(?string $tenantId, ?int $categoryId, string $name): ?ScopeRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        if ($categoryId !== null) {
            $condition->equals(self::COLUMN_CATEGORY_ID, $categoryId);
        }
        $condition->equals(self::COLUMN_NAME, $name);

        return $this->findOneBy($condition);
    }

    public function findByOperationId(?string $tenantId, int $operationId): array
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);
        $condition->equals(Generated\ScopeOperationTable::COLUMN_OPERATION_ID, $operationId);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'scope.' . self::COLUMN_NAME,
                'scope_operation.' . Generated\ScopeOperationTable::COLUMN_ALLOW,
            ])
            ->from('fusio_scope_operation', 'scope_operation')
            ->innerJoin('scope_operation', 'fusio_scope', 'scope', 'scope_operation.' . Generated\ScopeOperationTable::COLUMN_SCOPE_ID . ' = scope.' . self::COLUMN_ID)
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        return $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    public function findSubScopes(?string $tenantId, string $scope): array
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);
        $condition->like(self::COLUMN_NAME, $scope . '.%');

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'scope.' . self::COLUMN_ID,
                'scope.' . self::COLUMN_NAME,
                'scope.' . self::COLUMN_DESCRIPTION,
            ])
            ->from('fusio_scope', 'scope')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->orderBy('scope.' . self::COLUMN_NAME, 'ASC')
            ->setParameters($condition->getValues());

        return $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }

    /**
     * @return array<ScopeRow>
     */
    public function getValidScopes(?string $tenantId, array $names): array
    {
        $names = array_filter($names);

        if (!empty($names)) {
            $condition = Condition::withAnd();
            $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
            $condition->in(self::COLUMN_NAME, $names);
            $condition->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);
            return $this->findAll($condition, 0, 1024);
        } else {
            return [];
        }
    }

    public function getAvailableScopes(int $categoryId, ?string $tenantId = null): array
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_CATEGORY_ID, $categoryId);
        $condition->equals(self::COLUMN_STATUS, self::STATUS_ACTIVE);

        $result = $this->findAll($condition, 0, 1024, ScopeColumn::NAME, OrderBy::ASC);
        $scopes = [];
        foreach ($result as $row) {
            $scopes[$row->getName()] = $row->getDescription();
        }

        return $scopes;
    }

    public function getValidUserScopes(?string $tenantId, int $userId, ?array $scopes): array
    {
        if (empty($scopes)) {
            return [];
        }

        $userScopes = $this->getTable(User\Scope::class)->getAvailableScopes($tenantId, $userId);
        $scopes = $this->getValidScopes($tenantId, $scopes);

        // check that the user can assign only the scopes which are also assigned to the user account
        $scopes = array_filter($scopes, function (Generated\ScopeRow $scope) use ($userScopes) {
            foreach ($userScopes as $userScope) {
                if ($userScope['id'] == $scope->getId()) {
                    return true;
                }
            }
            return false;
        });

        return array_map(function (Generated\ScopeRow $scope) {
            return $scope->getName();
        }, $scopes);
    }

    public static function getNames(array $result): array
    {
        return array_map(function ($row) {
            return $row[self::COLUMN_NAME];
        }, $result);
    }
}

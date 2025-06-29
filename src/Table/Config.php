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

use Fusio\Impl\Table\Generated\CategoryRow;
use Fusio\Impl\Table\Generated\ConfigRow;
use PSX\Sql\Condition;

/**
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Config extends Generated\ConfigTable
{
    public const FORM_STRING   = 1;
    public const FORM_BOOLEAN  = 2;
    public const FORM_NUMBER   = 3;
    public const FORM_DATETIME = 4;
    public const FORM_EMAIL    = 5;
    public const FORM_TEXT     = 6;
    public const FORM_SECRET   = 7;

    public function findOneByIdentifier(?string $tenantId, string $id): ?ConfigRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByTenantAndName($tenantId, urldecode(substr($id, 1)));
        } else {
            return $this->findOneByTenantAndId($tenantId, (int) $id);
        }
    }

    public function findOneByTenantAndId(?string $tenantId, int $id): ?ConfigRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_ID, $id);

        return $this->findOneBy($condition);
    }

    public function findOneByTenantAndName(?string $tenantId, string $name): ?ConfigRow
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_NAME, $name);

        return $this->findOneBy($condition);
    }

    public function getValue(?string $tenantId, string $name): array|false
    {
        $condition = Condition::withAnd();
        $condition->equals(self::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(self::COLUMN_NAME, $name);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                'config.' . self::COLUMN_ID,
                'config.' . self::COLUMN_VALUE,
                'config.' . self::COLUMN_TYPE,
            ])
            ->from('fusio_config', 'config')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        return $this->connection->fetchAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
    }
}

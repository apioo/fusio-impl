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

namespace Fusio\Impl\Installation;

use Doctrine\DBAL\Connection;
use PSX\Sql\Condition;
use PSX\Sql\Test\ResolvableInterface;

/**
 * Reference
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Reference implements ResolvableInterface
{
    private string $tableName;
    private string $name;
    private ?string $tenantId;

    public function __construct(string $tableName, string $name, ?string $tenantId)
    {
        $this->tableName = $tableName;
        $this->name = $name;
        $this->tenantId = $tenantId;
    }

    public function resolve(Connection $connection): int
    {
        if ($this->tableName === 'fusio_page') {
            $column = 'slug';
        } else {
            $column = 'name';
        }

        $condition = Condition::withAnd();
        $condition->equals('tenant_id', $this->tenantId);
        $condition->equals($column, $this->name);

        $queryBuilder = $connection->createQueryBuilder()
            ->select(['target.id'])
            ->from($this->tableName, 'target')
            ->where($condition->getExpression($connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $id = (int) $connection->fetchOne($queryBuilder->getSQL(), $queryBuilder->getParameters());
        if (empty($id)) {
            throw new \RuntimeException('Could not resolve ' . $this->name . ' for table ' . $this->tableName);
        }

        return $id;
    }
}

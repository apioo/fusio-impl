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

namespace Fusio\Impl\Framework\Api\Scanner;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Api\Scanner\FilterFactory as PSXFilterFactory;
use PSX\Api\Scanner\FilterInterface;
use PSX\Sql\Condition;

/**
 * FilterFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class FilterFactory extends PSXFilterFactory
{
    private Connection $connection;
    private FrameworkConfig $frameworkConfig;
    private bool $loaded = false;

    public function __construct(Connection $connection, FrameworkConfig $frameworkConfig)
    {
        parent::__construct();

        $this->connection = $connection;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function getFilter(string $name): ?FilterInterface
    {
        $this->load();
        return parent::getFilter($name);
    }

    public function getDefault(): ?string
    {
        $this->load();
        return parent::getDefault();
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\CategoryTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\CategoryTable::COLUMN_ID,
                Table\Generated\CategoryTable::COLUMN_NAME,
            ])
            ->from('fusio_category', 'category')
            ->orderBy(Table\Generated\CategoryTable::COLUMN_ID, 'ASC')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $first = null;
        $result = $this->connection->fetchAllAssociative($queryBuilder->getSQL(), $queryBuilder->getParameters());
        foreach ($result as $row) {
            if ($first === null) {
                $first = $row[Table\Generated\CategoryTable::COLUMN_NAME];
            }

            $this->addFilter($row[Table\Generated\CategoryTable::COLUMN_NAME], new CategoryFilter((int) $row[Table\Generated\CategoryTable::COLUMN_ID]));
        }

        $this->addFilter('fusio', new CategoriesFilter([2, 3, 4, 5]));
        $this->addFilter('frontend', new CategoriesFilter([1, 3, 4, 5]));
        $this->addFilter('app', new CategoriesFilter([1, 5]));
        $this->setDefault('app');

        $this->loaded = true;
    }
}

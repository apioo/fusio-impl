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

namespace Fusio\Impl\Service\Agent;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * Loads information about the current instance which can be injected into an agent introduction, this allows an agent
 * to work with the real configuration of the instance without the need to call a tool
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class IntroductionContext
{
    /**
     * Max number of entries per list, this limits the size of the introduction
     */
    public const int MAX_ENTRIES = 128;

    public function __construct(
        private Table\Connection $connectionTable,
        private Table\Operation $operationTable,
        private Table\Schema $schemaTable,
    ) {
    }

    /**
     * @return list<array{name: string, class: string}>
     */
    public function getConnections(ContextInterface $context): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_STATUS, Table\Connection::STATUS_ACTIVE);

        $result = [];
        foreach ($this->connectionTable->findBy($condition, 0, self::MAX_ENTRIES, Table\Generated\ConnectionColumn::NAME, OrderBy::ASC) as $row) {
            $result[] = [
                'name' => $row->getName(),
                'class' => $row->getClass(),
            ];
        }

        return $result;
    }

    /**
     * @return list<array{name: string, httpMethod: string, httpPath: string, public: bool}>
     */
    public function getOperations(ContextInterface $context): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->notEquals(Table\Generated\OperationTable::COLUMN_STATUS, Table\Operation::STATUS_DELETED);

        $result = [];
        foreach ($this->operationTable->findBy($condition, 0, self::MAX_ENTRIES, Table\Generated\OperationColumn::HTTP_PATH, OrderBy::ASC) as $row) {
            $result[] = [
                'name' => $row->getName(),
                'httpMethod' => $row->getHttpMethod(),
                'httpPath' => $row->getHttpPath(),
                'public' => (bool) $row->getPublic(),
            ];
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    public function getSchemas(ContextInterface $context): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\SchemaTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\SchemaTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->equals(Table\Generated\SchemaTable::COLUMN_STATUS, Table\Schema::STATUS_ACTIVE);

        $result = [];
        foreach ($this->schemaTable->findBy($condition, 0, self::MAX_ENTRIES, Table\Generated\SchemaColumn::NAME, OrderBy::ASC) as $row) {
            $result[] = $row->getName();
        }

        return $result;
    }
}

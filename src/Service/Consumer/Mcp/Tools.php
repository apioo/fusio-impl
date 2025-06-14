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

namespace Fusio\Impl\Service\Consumer\Mcp;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use PSX\Sql\Condition;

/**
 * Tools
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Tools
{
    public function __construct(private Table\Operation $operationTable)
    {
    }

    public function list(ContextInterface $context): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, 1);
        $condition->equals(Table\Generated\OperationTable::COLUMN_ACTIVE, 1);

        $operations = $this->operationTable->findAll($condition);
        foreach ($operations as $operation) {

        }
    }
}

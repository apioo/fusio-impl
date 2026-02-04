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

use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;
use Symfony\AI\Agent\Toolbox\ToolFactoryInterface;
use Symfony\AI\Platform\Tool\ExecutionReference;
use Symfony\AI\Platform\Tool\Tool;

/**
 * OperationToolFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class OperationToolFactory implements ToolFactoryInterface
{
    public function __construct(
        private Table\Operation $operationTable,
        private InputSchemaBuilder $inputSchemaBuilder,
        private FrameworkConfig $frameworkConfig,
    ) {
    }

    public function getTool(string $reference): iterable
    {
        if ($reference !== OperationTool::class) {
            return [];
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, 1);
        $condition->equals(Table\Generated\OperationTable::COLUMN_ACTIVE, 1);

        $operations = $this->operationTable->findAll($condition, 0, 1024, Table\Generated\OperationColumn::NAME, OrderBy::ASC);
        foreach ($operations as $operation) {
            $inputSchema = $this->inputSchemaBuilder->build($operation);
            if (count($inputSchema) === 0) {
                continue;
            }

            $description = $operation->getDescription();
            if (empty($description)) {
                continue;
            }

            yield new Tool(
                new ExecutionReference(OperationTool::class, 'invoke'),
                ToolName::toToolName($operation->getName()),
                $description,
                $inputSchema
            );
        }
    }
}

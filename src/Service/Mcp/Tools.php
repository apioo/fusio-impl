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

namespace Fusio\Impl\Service\Mcp;

use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service\JsonRPC;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Mcp\Types\CallToolRequestParams;
use Mcp\Types\CallToolResult;
use Mcp\Types\ListToolsResult;
use Mcp\Types\PaginatedRequestParams;
use Mcp\Types\TextContent;
use Mcp\Types\Tool;
use PSX\Record\Record;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * Tools
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Tools
{
    public function __construct(
        private Tools\Builder $builder,
        private Tools\Naming $naming,
        private ActiveUser $activeUser,
        private JsonRPC\Invoker $invoker,
        private Table\Operation $operationTable,
        private FrameworkConfig $frameworkConfig,
        private ContextFactory $contextFactory,
    ) {
    }

    public function list(PaginatedRequestParams $params): ListToolsResult
    {
        $cursor = $params->cursor ?? null;

        $user = $this->activeUser->getUser();
        $categoryId = $user?->getCategoryId();

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        if ($categoryId !== null) {
            $condition->equals(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $categoryId);
        }
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, 1);
        $condition->equals(Table\Generated\OperationTable::COLUMN_ACTIVE, 1);

        $count = 32;
        $startIndex = empty($cursor) ? 0 : ((int) base64_decode($cursor));
        $nextCursor = base64_encode('' . ($startIndex + $count));

        $tools = [];
        $operations = $this->operationTable->findAll($condition, $startIndex, $count, Table\Generated\OperationColumn::ID, OrderBy::DESC);
        foreach ($operations as $operation) {
            $tool = $this->builder->build($operation);
            if (!$tool instanceof Tool) {
                continue;
            }

            $tools[] = $tool;
        }

        if (count($tools) === 0) {
            $nextCursor = null;
        }

        return new ListToolsResult($tools, $nextCursor);
    }

    public function call(CallToolRequestParams $params): CallToolResult
    {
        try {
            $rawArguments = $params->arguments;
            if (is_array($rawArguments)) {
                $arguments = Record::from($rawArguments);
            } else {
                $arguments = new Record();
            }

            $context = $this->contextFactory->getActive();
            $context->setTenantId($this->frameworkConfig->getTenantId());

            $response = $this->invoker->invoke(
                $this->naming->toOperationId($params->name),
                $arguments,
                $this->activeUser->getBearerToken(),
                null,
                $context,
            );

            $text = (string) $response->getBody();

            return new CallToolResult([new TextContent($text)]);
        } catch (\Throwable $e) {
            return new CallToolResult([new TextContent('Failed to execute ' . $params->name . ': ' . $e->getMessage())], isError: true);
        }
    }
}

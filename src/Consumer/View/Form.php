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

namespace Fusio\Impl\Consumer\View;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Service\Form\JsonSchemaResolver;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\OrderBy;
use PSX\Sql\TableManager;
use PSX\Sql\ViewAbstract;

/**
 * Form
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Form extends ViewAbstract
{
    private JsonSchemaResolver $jsonSchemaResolver;
    private FrameworkConfig $config;

    public function __construct(TableManager $tableManager, JsonSchemaResolver $jsonSchemaResolver, FrameworkConfig $config)
    {
        parent::__construct($tableManager);

        $this->jsonSchemaResolver = $jsonSchemaResolver;
        $this->config = $config;
    }

    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = Table\Generated\FormColumn::tryFrom($filter->getSortBy(Table\Generated\FormTable::COLUMN_NAME) ?? '');
        $sortOrder = $filter->getSortOrder(OrderBy::ASC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\FormTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\FormTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\FormTable::COLUMN_STATUS, Table\Form::STATUS_ACTIVE);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Form::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Form::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\FormTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\FormTable::COLUMN_STATUS),
                'name' => Table\Generated\FormTable::COLUMN_NAME,
                'metadata' => $builder->fieldJson(Table\Generated\FormTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Form::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\FormTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\FormTable::COLUMN_STATUS),
            'name' => Table\Generated\FormTable::COLUMN_NAME,
            'action' => $builder->fieldCallback(Table\Generated\FormTable::COLUMN_OPERATION_ID, function ($operationId) {
                $table = $this->getTable(Table\Generated\OperationTable::class)->find($operationId);
                return $this->config->getUrl(ltrim($table->getHttpPath(), '/'));
            }),
            'method' => $builder->fieldCallback(Table\Generated\FormTable::COLUMN_OPERATION_ID, function ($operationId) {
                $table = $this->getTable(Table\Generated\OperationTable::class)->find($operationId);
                return $table->getHttpMethod();
            }),
            'jsonSchema' => $builder->fieldCallback(Table\Generated\FormTable::COLUMN_OPERATION_ID, function ($operationId) {
                return $this->jsonSchemaResolver->resolve($operationId);
            }),
            'uiSchema' => $builder->fieldJson(Table\Generated\FormTable::COLUMN_UI_SCHEMA),
            'metadata' => $builder->fieldJson(Table\Generated\FormTable::COLUMN_METADATA),
        ]);

        return $builder->build($definition);
    }
}

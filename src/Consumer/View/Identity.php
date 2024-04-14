<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Nested\Builder;
use PSX\Sql\OrderBy;
use PSX\Sql\TableManager;
use PSX\Sql\ViewAbstract;

/**
 * Event
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Identity extends ViewAbstract
{
    private FrameworkConfig $frameworkConfig;

    public function __construct(TableManager $tableManager, FrameworkConfig $frameworkConfig)
    {
        parent::__construct($tableManager);

        $this->frameworkConfig = $frameworkConfig;
    }

    public function getCollection(?int $appId, ?string $appKey, QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = $filter->getSortBy(Table\Generated\IdentityTable::COLUMN_NAME);
        $sortOrder = $filter->getSortOrder(OrderBy::ASC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\IdentityTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\IdentityTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\IdentityTable::COLUMN_STATUS, Table\Event::STATUS_ACTIVE);

        if (!empty($appId)) {
            $condition->equals(Table\Generated\IdentityTable::COLUMN_APP_ID, $appId);
        }

        if (!empty($appKey)) {
            $appId = $this->getTable(Table\App::class)->findOneByTenantAndAppKey($context->getTenantId(), $appKey)?->getId();
            if (empty($appId)) {
                throw new StatusCode\BadRequestException('Provided app key does not exist');
            }

            $condition->equals(Table\Generated\IdentityTable::COLUMN_APP_ID, $appId);
        }

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Identity::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Identity::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\IdentityTable::COLUMN_ID),
                'name' => Table\Generated\IdentityTable::COLUMN_NAME,
                'icon' => Table\Generated\IdentityTable::COLUMN_ICON,
                'redirect' => $builder->fieldCallback(Table\Generated\IdentityTable::COLUMN_ID, function($id) {
                    return $this->frameworkConfig->getDispatchUrl('consumer', 'identity', $id, 'redirect');
                }),
            ]),
        ];

        return $builder->build($definition);
    }
}

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

namespace Fusio\Impl\Consumer\View\User;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * Grant
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Grant extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();

        $condition = Condition::withAnd();
        $condition->equals('user_grant.user_id', $context->getUser()->getId());
        $condition->equals('app.tenant_id', $context->getTenantId());
        $condition->equals('app.status', Table\App::STATUS_ACTIVE);

        $countSql = $this->getBaseQuery(['COUNT(*) AS cnt'], $condition);
        $querySql = $this->getBaseQuery(['user_grant.id', 'user_grant.allow', 'user_grant.date', 'user_grant.app_id', 'app.name AS app_name', 'app.url AS app_url'], $condition, 'user_grant.id DESC');
        $querySql = $this->connection->getDatabasePlatform()->modifyLimitQuery($querySql, $count, $startIndex);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $builder->doValue($countSql, $condition->getValues(), $builder->fieldInteger('cnt')),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection($querySql, $condition->getValues(), [
                'id' => $builder->fieldInteger('id'),
                'allow' => $builder->fieldInteger('allow'),
                'createDate' => $builder->fieldDateTime('date'),
                'app' => [
                    'id' => $builder->fieldInteger('app_id'),
                    'name' => 'app_name',
                    'url' => 'app_url',
                ],
            ]),
        ];

        return $builder->build($definition);
    }

    private function getBaseQuery(array $fields, Condition $condition, ?string $orderBy = null)
    {
        $fields  = implode(',', $fields);
        $where   = $condition->getStatement($this->connection->getDatabasePlatform());
        $orderBy = $orderBy !== null ? 'ORDER BY ' . $orderBy : '';

        return <<<SQL
    SELECT {$fields}
      FROM fusio_user_grant user_grant
INNER JOIN fusio_app app
        ON user_grant.app_id = app.id
           {$where}
           {$orderBy}
SQL;
    }
}

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

namespace Fusio\Impl\Backend\View\Dashboard;

use Fusio\Engine\ContextInterface;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;
use Fusio\Impl\Table;

/**
 * LatestRequests
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class LatestRequests extends ViewAbstract
{
    public function getView(ContextInterface $context)
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\LogTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\LogTable::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());

        $sql = '  SELECT log.id,
                         log.path,
                         log.ip,
                         log.date
                    FROM fusio_log log
                         ' . $condition->getStatement($this->connection->getDatabasePlatform()) . '
                ORDER BY log.id DESC';

        $sql = $this->connection->getDatabasePlatform()->modifyLimitQuery($sql, 6);
        $builder = new Builder($this->connection);

        $definition = [
            'entry' => $builder->doCollection($sql, $condition->getValues(), [
                'id' => $builder->fieldInteger('id'),
                'path' => 'path',
                'ip' => 'ip',
                'date' => $builder->fieldDateTime('date'),
            ]),
        ];

        return $builder->build($definition);
    }
}

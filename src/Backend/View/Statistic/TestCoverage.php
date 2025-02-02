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

namespace Fusio\Impl\Backend\View\Statistic;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\ViewAbstract;

/**
 * TestCoverage
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TestCoverage extends ViewAbstract
{
    public function getView(ContextInterface $context)
    {
        $status = [
            Table\Test::STATUS_PENDING => 'Pending',
            Table\Test::STATUS_SUCCESS => 'Success',
            Table\Test::STATUS_WARNING => 'Warning',
            Table\Test::STATUS_ERROR => 'Error',
        ];

        $data = [];
        $labels = [];

        foreach ($status as $key => $label) {
            $condition = Condition::withAnd();
            $condition->equals(Table\Test::COLUMN_TENANT_ID, $context->getTenantId());
            $condition->equals(Table\Test::COLUMN_CATEGORY_ID, $context->getUser()->getCategoryId());
            $condition->equals(Table\Test::COLUMN_STATUS, $key);

            $expression = $condition->getExpression($this->connection->getDatabasePlatform());

            $count = (int) $this->connection->fetchOne('SELECT COUNT(*) AS cnt FROM fusio_test WHERE ' . $expression, $condition->getValues());

            $data[] = $count;
            $labels[] = $label;
        }

        return [
            'labels' => $labels,
            'data' => [$data],
        ];
    }
}

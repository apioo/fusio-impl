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

namespace Fusio\Impl\Backend\View;

use Fusio\Engine\ContextInterface;
use Fusio\Engine\Form;
use Fusio\Impl\Backend\Filter\QueryFilter;
use Fusio\Impl\Provider\ConnectionProvider;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Nested\Builder;
use PSX\Sql\OrderBy;
use PSX\Sql\ViewAbstract;

/**
 * Connection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Connection extends ViewAbstract
{
    public function getCollection(QueryFilter $filter, ContextInterface $context)
    {
        $startIndex = $filter->getStartIndex();
        $count = $filter->getCount();
        $sortBy = $filter->getSortBy(Table\Generated\ConnectionTable::COLUMN_ID);
        $sortOrder = $filter->getSortOrder(OrderBy::DESC);

        $condition = $filter->getCondition([QueryFilter::COLUMN_SEARCH => Table\Generated\ConnectionTable::COLUMN_NAME]);
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_STATUS, Table\Connection::STATUS_ACTIVE);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Connection::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Connection::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $builder->fieldInteger(Table\Generated\ConnectionTable::COLUMN_ID),
                'status' => $builder->fieldInteger(Table\Generated\ConnectionTable::COLUMN_STATUS),
                'name' => Table\Generated\ConnectionTable::COLUMN_NAME,
                'metadata' => $builder->fieldJson(Table\Generated\ConnectionTable::COLUMN_METADATA),
            ]),
        ];

        return $builder->build($definition);
    }

    public function getEntity(string $id, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Connection::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\ConnectionTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\ConnectionTable::COLUMN_STATUS),
            'name' => Table\Generated\ConnectionTable::COLUMN_NAME,
            'class' => Table\Generated\ConnectionTable::COLUMN_CLASS,
            'metadata' => $builder->fieldJson(Table\Generated\ConnectionTable::COLUMN_METADATA),
        ]);

        return $builder->build($definition);
    }

    public function getEntityWithConfig(string $id, string $secretKey, ConnectionProvider $connectionProvider, ContextInterface $context)
    {
        $builder = new Builder($this->connection);

        $definition = $builder->doEntity([$this->getTable(Table\Connection::class), 'findOneByIdentifier'], [$context->getTenantId(), $id], [
            'id' => $builder->fieldInteger(Table\Generated\ConnectionTable::COLUMN_ID),
            'status' => $builder->fieldInteger(Table\Generated\ConnectionTable::COLUMN_STATUS),
            'name' => Table\Generated\ConnectionTable::COLUMN_NAME,
            'class' => Table\Generated\ConnectionTable::COLUMN_CLASS,
            'config' => $builder->fieldCallback(Table\Generated\ConnectionTable::COLUMN_CONFIG, function ($config, $row) use ($secretKey, $connectionProvider) {
                $config = Service\Connection\Encrypter::decrypt($config, $secretKey);

                // remove all password fields from the config
                if (!empty($config)) {
                    $form = $connectionProvider->getForm($row[Table\Generated\ConnectionTable::COLUMN_CLASS]);
                    if ($form instanceof Form\Container) {
                        $elements = $form->getElements();
                        foreach ($elements as $element) {
                            if ($element instanceof Form\Element\Input && $element->getType() == 'password') {
                                if (isset($config[$element->getName()])) {
                                    unset($config[$element->getName()]);
                                }
                            }
                        }
                    }

                    return (object) $config;
                } else {
                    return new \stdClass();
                }
            }),
            'metadata' => $builder->fieldJson(Table\Generated\ConnectionTable::COLUMN_METADATA),
        ]);

        return $builder->build($definition);
    }
}

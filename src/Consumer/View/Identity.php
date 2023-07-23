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

use Fusio\Impl\Table;
use PSX\Framework\Config\ConfigInterface;
use PSX\Nested\Builder;
use PSX\Sql\Condition;
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
    private ConfigInterface $config;

    public function __construct(TableManager $tableManager, ConfigInterface $config)
    {
        parent::__construct($tableManager);

        $this->config = $config;
    }

    public function getCollection(int $userId, ?int $appId, int $startIndex = 0)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($appId)) {
            // by default we use the consumer app
            $appId = 2;
        }

        $count = 16;

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\IdentityTable::COLUMN_STATUS, Table\Event::STATUS_ACTIVE);
        $condition->equals(Table\Generated\IdentityTable::COLUMN_APP_ID, $appId);

        $builder = new Builder($this->connection);

        $definition = [
            'totalResults' => $this->getTable(Table\Identity::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $builder->doCollection([$this->getTable(Table\Identity::class), 'findAll'], [$condition, $startIndex, $count, 'name', OrderBy::ASC], [
                'id' => $builder->fieldInteger(Table\Generated\IdentityTable::COLUMN_ID),
                'name' => Table\Generated\IdentityTable::COLUMN_NAME,
                'icon' => Table\Generated\IdentityTable::COLUMN_ICON,
                'redirect' => $builder->fieldCallback(Table\Generated\IdentityTable::COLUMN_ID, function($id) {
                    return $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch') . 'consumer/identity/' . $id . '/redirect';
                }),
            ]),
        ];

        return $builder->build($definition);
    }
}

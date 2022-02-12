<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Backend\View;

use Fusio\Engine\Form;
use Fusio\Engine\Parser\ParserInterface;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\Sql;
use PSX\Sql\ViewAbstract;

/**
 * Connection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Connection extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, ?string $search = null, ?string $sortBy = null, ?string $sortOrder = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        if ($sortBy === null) {
            $sortBy = Table\Generated\ConnectionTable::COLUMN_ID;
        }

        if ($sortOrder === null) {
            $sortOrder = Sql::SORT_DESC;
        }

        $condition = new Condition();
        $condition->equals(Table\Generated\ConnectionTable::COLUMN_STATUS, Table\Connection::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like(Table\Generated\ConnectionTable::COLUMN_NAME, '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Connection::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Connection::class), 'findAll'], [$condition, $startIndex, $count, $sortBy, $sortOrder], [
                'id' => $this->fieldInteger(Table\Generated\ConnectionTable::COLUMN_ID),
                'status' => $this->fieldInteger(Table\Generated\ConnectionTable::COLUMN_STATUS),
                'name' => Table\Generated\ConnectionTable::COLUMN_NAME,
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity(string $id)
    {
        if (str_starts_with($id, '~')) {
            $method = 'findOneByName';
            $id = urldecode(substr($id, 1));
        } else {
            $method = 'find';
            $id = (int) $id;
        }

        $definition = $this->doEntity([$this->getTable(Table\Connection::class), $method], [$id], [
            'id' => $this->fieldInteger(Table\Generated\ConnectionTable::COLUMN_ID),
            'status' => $this->fieldInteger(Table\Generated\ConnectionTable::COLUMN_STATUS),
            'name' => Table\Generated\ConnectionTable::COLUMN_NAME,
            'class' => Table\Generated\ConnectionTable::COLUMN_CLASS,
        ]);

        return $this->build($definition);
    }

    public function getEntityWithConfig(string $id, string $secretKey, ParserInterface $connectionParser)
    {
        if (str_starts_with($id, '~')) {
            $method = 'findOneByName';
            $id = urldecode(substr($id, 1));
        } else {
            $method = 'find';
            $id = (int) $id;
        }

        $definition = $this->doEntity([$this->getTable(Table\Connection::class), $method], [$id], [
            'id' => $this->fieldInteger(Table\Generated\ConnectionTable::COLUMN_ID),
            'status' => $this->fieldInteger(Table\Generated\ConnectionTable::COLUMN_STATUS),
            'name' => Table\Generated\ConnectionTable::COLUMN_NAME,
            'class' => Table\Generated\ConnectionTable::COLUMN_CLASS,
            'config' => $this->fieldCallback(Table\Generated\ConnectionTable::COLUMN_CONFIG, function ($config, $row) use ($secretKey, $connectionParser) {
                $config = Service\Connection\Encrypter::decrypt($config, $secretKey);

                // remove all password fields from the config
                if (!empty($config) && is_array($config)) {
                    $form = $connectionParser->getForm($row[Table\Generated\ConnectionTable::COLUMN_CLASS]);
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
        ]);

        return $this->build($definition);
    }
}

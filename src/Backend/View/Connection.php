<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * @link    http://fusio-project.org
 */
class Connection extends ViewAbstract
{
    public function getCollection(int $startIndex, int $count, ?string $search = null)
    {
        if (empty($startIndex) || $startIndex < 0) {
            $startIndex = 0;
        }

        if (empty($count) || $count < 1 || $count > 1024) {
            $count = 16;
        }

        $condition = new Condition();
        $condition->equals('status', Table\Connection::STATUS_ACTIVE);

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        $definition = [
            'totalResults' => $this->getTable(Table\Connection::class)->getCount($condition),
            'startIndex' => $startIndex,
            'itemsPerPage' => $count,
            'entry' => $this->doCollection([$this->getTable(Table\Connection::class), 'getAll'], [$startIndex, $count, 'id', Sql::SORT_DESC, $condition, Fields::blacklist(['class', 'config'])], [
                'id' => $this->fieldInteger('id'),
                'status' => $this->fieldInteger('status'),
                'name' => 'name',
            ]),
        ];

        return $this->build($definition);
    }

    public function getEntity($id)
    {
        $definition = $this->doEntity([$this->getTable(Table\Connection::class), 'get'], [$id], [
            'id' => $this->fieldInteger('id'),
            'status' => $this->fieldInteger('status'),
            'name' => 'name',
            'class' => 'class',
        ]);

        return $this->build($definition);
    }

    public function getEntityWithConfig($id, $secretKey, ParserInterface $connectionParser)
    {
        $definition = $this->doEntity([$this->getTable(Table\Connection::class), 'get'], [$id], [
            'id' => $this->fieldInteger('id'),
            'status' => $this->fieldInteger('status'),
            'name' => 'name',
            'class' => 'class',
            'config' => $this->fieldCallback('config', function ($config, $row) use ($secretKey, $connectionParser) {
                $config = Service\Connection\Encrypter::decrypt($config, $secretKey);

                // remove all password fields from the config
                if (!empty($config) && is_array($config)) {
                    $form = $connectionParser->getForm($row['class']);
                    if ($form instanceof Form\Container) {
                        $elements = $form->getElements();
                        foreach ($elements as $element) {
                            if ($element instanceof Form\Element\Input && $element['type'] == 'password') {
                                if (isset($config[$element['name']])) {
                                    unset($config[$element['name']]);
                                }
                            }
                        }
                    }
                } else {
                    $config = new \stdClass();
                }
                
                return $config;
            }),
        ]);

        return $this->build($definition);
    }
}

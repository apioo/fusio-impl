<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Service\System;

use Fusio\Impl\Form\Element;
use PSX\Json\Parser;
use ReflectionClass;
use RuntimeException;
use stdClass;

/**
 * System
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Import extends SystemAbstract
{
    public function import($data)
    {
        $data   = Parser::decode($data, false);
        $result = [];

        if (!$data instanceof stdClass) {
            throw new RuntimeException('Data must be an object');
        }

        $classes = isset($data->actionClass) ? $data->actionClass : null;
        if (!empty($classes) && is_array($classes)) {
            foreach ($classes as $class) {
                $this->insertClass('fusio_action_class', $class, 'Fusio\Engine\ActionInterface');
            }
        }

        $classes = isset($data->connectionClass) ? $data->connectionClass : null;
        if (!empty($classes) && is_array($classes)) {
            foreach ($classes as $class) {
                $this->insertClass('fusio_connection_class', $class, 'Fusio\Engine\ConnectionInterface');
            }
        }

        foreach ($this->types as $type) {
            if (isset($data->$type) && is_array($data->$type)) {
                foreach ($data->$type as $entry) {
                    $result[] = $this->importType($type, $entry);
                }
            }
        }

        return $result;
    }

    protected function importType($type, stdClass $data)
    {
        $response = $this->doRequest('POST', $type, $this->transform($type, $data));

        if ($type == 'routes') {
            $title = $data->path;
        } else {
            $title = $data->name;
        }

        if (isset($response->success) && $response->success === false) {
            $this->logger->error($response->message);

            return '[SKIPPED] ' . $type . ' ' . $title;
        } else {
            return '[CREATED] ' . $type . ' ' . $title;
        }
    }

    protected function insertClass($tableName, $className, $interface)
    {
        if (!is_string($className)) {
            throw new RuntimeException('Class name must be a string ' . gettype($className) . ' given');
        }

        $class = new ReflectionClass($className);

        if ($class->implementsInterface($interface)) {
            $id = $this->connection->fetchColumn('SELECT id FROM ' . $tableName . ' WHERE class = :class', [
                'class' => $class->getName(),
            ]);

            if (empty($id)) {
                $this->connection->insert($tableName, [
                    'class' => $class->getName(),
                ]);
            }
        } else {
            throw new RuntimeException('Class ' . $class->getName() . ' must implement the interface ' . $interface);
        }
    }

    protected function getReference($tableName, $name)
    {
        return $this->connection->fetchColumn('SELECT id FROM ' . $tableName . ' WHERE name = :name', ['name' => $name]);
    }
}

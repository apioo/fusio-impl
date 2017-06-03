<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ConnectionInterface;
use PSX\Json\Parser;
use ReflectionClass;
use RuntimeException;
use stdClass;

/**
 * System
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
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
                $this->importClass('fusio_action_class', $class, ActionInterface::class);
            }
        }

        $classes = isset($data->connectionClass) ? $data->connectionClass : null;
        if (!empty($classes) && is_array($classes)) {
            foreach ($classes as $class) {
                $this->importClass('fusio_connection_class', $class, ConnectionInterface::class);
            }
        }

        $config = isset($data->config) ? $data->config : null;
        if (!empty($config) && $config instanceof stdClass) {
            $this->importConfig($config);
        }

        if (isset($data->connection) && is_array($data->connection)) {
            foreach ($data->connection as $entry) {
                $result[] = $this->importGeneral(self::TYPE_CONNECTION, $entry);
            }
        }

        if (isset($data->schema) && is_array($data->schema)) {
            foreach ($data->schema as $entry) {
                $result[] = $this->importGeneral(self::TYPE_SCHEMA, $entry);
            }
        }

        if (isset($data->action) && is_array($data->action)) {
            foreach ($data->action as $entry) {
                $result[] = $this->importGeneral(self::TYPE_ACTION, $entry);
            }
        }

        if (isset($data->routes) && is_array($data->routes)) {
            foreach ($data->routes as $entry) {
                $result[] = $this->importRoutes($entry);
            }
        }

        return $result;
    }

    protected function importGeneral($type, stdClass $data)
    {
        $name = $data->name;
        $id   = $this->connection->fetchColumn('SELECT id FROM fusio_' . $type . ' WHERE name = :name', [
            'name' => $name
        ]);

        if (!empty($id)) {
            $response = $this->doRequest('PUT', $type . '/' . $id, $this->transform($type, $data));
        } else {
            $response = $this->doRequest('POST', $type, $this->transform($type, $data));
        }

        if (isset($response->success) && $response->success === false) {
            $this->logger->error($response->message);

            return '[ERROR] ' . $type . ' ' . $name . ': ' . $response->message;
        } elseif (!empty($id)) {
            return '[UPDATED] ' . $type . ' ' . $name;
        } else {
            return '[CREATED] ' . $type . ' ' . $name;
        }
    }

    protected function importRoutes(stdClass $data)
    {
        $path = $data->path;
        $id   = $this->connection->fetchColumn('SELECT id FROM fusio_routes WHERE path = :path', [
            'path' => $path
        ]);

        if (!empty($id)) {
            $response = $this->doRequest('PUT', 'routes/' . $id, $this->transform('routes', $data));
        } else {
            $response = $this->doRequest('POST', 'routes', $this->transform('routes', $data));
        }

        if (isset($response->success) && $response->success === false) {
            $this->logger->error($response->message);

            return '[ERROR] routes ' . $path . ': ' . $response->message;
        } elseif (!empty($id)) {
            return '[UPDATED] routes ' . $path;
        } else {
            return '[CREATED] routes ' . $path;
        }
    }

    protected function importConfig(stdClass $config)
    {
        foreach ($config as $name => $value) {
            if (!is_scalar($value)) {
                throw new RuntimeException('Config value must be scalar');
            }

            $id = $this->connection->fetchColumn('SELECT id FROM fusio_config WHERE name = :name', [
                'name' => $name,
            ]);

            if (!empty($id)) {
                $this->connection->update('fusio_config', [
                    'value' => $value,
                ], [
                    'id' => $id
                ]);
            } else {
                throw new RuntimeException('Unknown config parameter ' . $name);
            }
        }
    }

    protected function importClass($tableName, $className, $interface)
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

    protected function getReference($tableName, $name, $type)
    {
        return (int) $this->connection->fetchColumn('SELECT id FROM ' . $tableName . ' WHERE name = :name', ['name' => $name]);
    }
}

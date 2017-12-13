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
use Fusio\Impl\Service\System\Import\Result;
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
        $result = new Result();

        if (!$data instanceof stdClass) {
            throw new RuntimeException('Data must be an object');
        }

        $classes = isset($data->actionClass) ? $data->actionClass : null;
        if (!empty($classes) && is_array($classes)) {
            foreach ($classes as $class) {
                $this->importClass('fusio_action_class', $class, ActionInterface::class, $result);
            }
        }

        $classes = isset($data->connectionClass) ? $data->connectionClass : null;
        if (!empty($classes) && is_array($classes)) {
            foreach ($classes as $class) {
                $this->importClass('fusio_connection_class', $class, ConnectionInterface::class, $result);
            }
        }

        $config = isset($data->config) ? $data->config : null;
        if (!empty($config) && $config instanceof stdClass) {
            $this->importConfig($config, $result);
        }

        if (isset($data->connection) && is_array($data->connection)) {
            foreach ($data->connection as $entry) {
                $this->importGeneral(self::TYPE_CONNECTION, $entry, $result);
            }
        }

        if (isset($data->schema) && is_array($data->schema)) {
            foreach ($data->schema as $entry) {
                $this->importGeneral(self::TYPE_SCHEMA, $entry, $result);
            }
        }

        if (isset($data->action) && is_array($data->action)) {
            foreach ($data->action as $entry) {
                $this->importGeneral(self::TYPE_ACTION, $entry, $result);
            }
        }

        if (isset($data->routes) && is_array($data->routes)) {
            foreach ($data->routes as $entry) {
                $this->importRoutes($entry, $result);
            }
        }

        if (isset($data->cronjob) && is_array($data->cronjob)) {
            foreach ($data->cronjob as $entry) {
                $this->importGeneral(self::TYPE_CRONJOB, $entry, $result);
            }
        }

        return $result;
    }

    protected function importGeneral($type, stdClass $data, Result $result)
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

            $result->add($type, Result::ACTION_FAILED, $name . ': ' . $response->message);
        } elseif (!empty($id)) {
            $result->add($type, Result::ACTION_UPDATED, $name);
        } else {
            $result->add($type, Result::ACTION_CREATED, $name);
        }
    }

    protected function importRoutes(stdClass $data, Result $result)
    {
        $path = $data->path;
        $id   = $this->connection->fetchColumn('SELECT id FROM fusio_routes WHERE path = :path', [
            'path' => $path
        ]);

        if (!empty($id)) {
            $response = $this->doRequest('PUT', 'routes/' . $id, $this->transform(self::TYPE_ROUTES, $data));
        } else {
            $response = $this->doRequest('POST', 'routes', $this->transform(self::TYPE_ROUTES, $data));
        }

        if (isset($response->success) && $response->success === false) {
            $this->logger->error($response->message);

            $result->add(self::TYPE_ROUTES, Result::ACTION_FAILED, $path . ': ' . $response->message);
        } elseif (!empty($id)) {
            $result->add(self::TYPE_ROUTES, Result::ACTION_UPDATED, $path);
        } else {
            $result->add(self::TYPE_ROUTES, Result::ACTION_CREATED, $path);
        }
    }

    protected function importConfig(stdClass $config, Result $result)
    {
        $count = 0;
        foreach ($config as $name => $value) {
            if (!is_scalar($value)) {
                throw new RuntimeException('Config value must be scalar');
            }

            $id = $this->connection->fetchColumn('SELECT id FROM fusio_config WHERE name = :name', [
                'name' => $name,
            ]);

            if (!empty($id)) {
                $count+= $this->connection->update('fusio_config', [
                    'value' => $value,
                ], [
                    'id' => $id
                ]);
            } else {
                throw new RuntimeException('Unknown config parameter ' . $name);
            }
        }

        if ($count > 0) {
            $result->add(self::TYPE_CONFIG, Result::ACTION_UPDATED, 'Changed ' . $count . ' values');
        }
    }

    protected function importClass($tableName, $className, $interface, Result $result)
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

                $result->add('class', Result::ACTION_REGISTERED, $className);
            }
        } else {
            throw new RuntimeException('Class ' . $class->getName() . ' must implement the interface ' . $interface);
        }
    }

    protected function getReference($tableName, $name, $type)
    {
        $id = (int) $this->connection->fetchColumn('SELECT id FROM ' . $tableName . ' WHERE name = :name', ['name' => $name]);

        if (empty($id)) {
            $type = substr($tableName, 6);
            throw new \RuntimeException('Could not resolve ' . $type . ' ' . $name);
        }

        return $id;
    }
}

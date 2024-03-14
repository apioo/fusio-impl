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

namespace Fusio\Impl\Installation;

use Doctrine\DBAL\Connection;

/**
 * DataSyncronizer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class DataSyncronizer
{
    /**
     * Method which checks all entries from the new installation data to the current installation and inserts
     * missing entries, note it does not update existing entries
     */
    public static function sync(Connection $connection): void
    {
        $data = NewInstallation::getData();

        $configs = $data->getData('fusio_config');
        foreach ($configs as $config) {
            $configId = $connection->fetchOne('SELECT id FROM fusio_config WHERE tenant_id IS NULL AND name = :name', [
                'name' => $config['name']
            ]);

            if (empty($configId)) {
                self::insert($connection, 'fusio_config', $config);
            }
        }

        $operations = $data->getData('fusio_operation');
        $operationMap = [];
        foreach ($operations as $row) {
            $operationId = $connection->fetchOne('SELECT id FROM fusio_operation WHERE tenant_id IS NULL AND name = :name', [
                'name' => $row['name']
            ]);

            if (empty($operationId)) {
                self::insert($connection, 'fusio_operation', $row);
            }

            $id = $data->getReference('fusio_operation', $row['name'], null)->resolve($connection);

            $operationMap[$id] = $operationId;
        }

        $actions = $data->getData('fusio_action');
        foreach ($actions as $action) {
            $actionId = $connection->fetchOne('SELECT id FROM fusio_action WHERE tenant_id IS NULL AND name = :name', [
                'name' => $action['name']
            ]);

            if (empty($actionId)) {
                self::insert($connection, 'fusio_action', $action);
            }
        }

        $schemas = $data->getData('fusio_schema');
        foreach ($schemas as $schema) {
            $schemaId = $connection->fetchOne('SELECT id FROM fusio_schema WHERE tenant_id IS NULL AND name = :name', [
                'name' => $schema['name']
            ]);

            if (empty($schemaId)) {
                self::insert($connection, 'fusio_schema', $schema);
            }
        }

        $events = $data->getData('fusio_event');
        foreach ($events as $event) {
            $eventId = $connection->fetchOne('SELECT id FROM fusio_event WHERE tenant_id IS NULL AND name = :name', [
                'name' => $event['name']
            ]);

            if (empty($eventId)) {
                self::insert($connection, 'fusio_event', $event);
            }
        }

        $cronjobs = $data->getData('fusio_cronjob');
        foreach ($cronjobs as $cronjob) {
            $cronjobId = $connection->fetchOne('SELECT id FROM fusio_cronjob WHERE tenant_id IS NULL AND name = :name', [
                'name' => $cronjob['name']
            ]);

            if (empty($cronjobId)) {
                self::insert($connection, 'fusio_cronjob', $cronjob);
            }
        }

        $scopes = $data->getData('fusio_scope');
        $scopeMap = [];
        foreach ($scopes as $scope) {
            $scopeId = $connection->fetchOne('SELECT id FROM fusio_scope WHERE tenant_id IS NULL AND name = :name', [
                'name' => $scope['name']
            ]);

            if (empty($scopeId)) {
                self::insert($connection, 'fusio_scope', $scope);
                $scopeId = $connection->lastInsertId();
            }

            $id = $data->getReference('fusio_scope', $scope['name'], null)->resolve($connection);

            $scopeMap[$id] = $scopeId;
        }

        $scopeOperations = $data->getData('fusio_scope_operation');
        foreach ($scopeOperations as $scopeOperation) {
            $scopeId = $scopeMap[$scopeOperation['scope_id']->resolve($connection)] ?? null;
            $operationId = $operationMap[$scopeOperation['operation_id']->resolve($connection)] ?? null;

            if (empty($scopeId) || empty($operationId)) {
                continue;
            }

            $id = $connection->fetchOne('SELECT id FROM fusio_scope_operation WHERE scope_id = :scope AND operation_id = :operation', [
                'scope' => $scopeId,
                'operation' => $operationId,
            ]);

            if (empty($id)) {
                $scopeOperation['scope_id'] = $scopeId;
                $scopeOperation['operation_id'] = $operationId;

                self::insert($connection, 'fusio_scope_operation', $scopeOperation);
            }
        }
    }

    private static function insert(Connection $connection, string $tableName, array $data): void
    {
        $row = [];
        $columns = $connection->createSchemaManager()->listTableColumns($tableName);
        foreach ($columns as $column) {
            if (isset($data[$column->getName()])) {
                $row[$column->getName()] = $data[$column->getName()];
            }
        }

        foreach ($row as $key => $value) {
            if ($value instanceof Reference) {
                $row[$key] = $value->resolve($connection);
            }
        }

        $connection->insert($tableName, $row);
    }
}

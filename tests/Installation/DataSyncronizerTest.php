<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

namespace Fusio\Impl\Tests\Installation;

use Fusio\Impl\Backend;
use Fusio\Impl\Installation\DataSyncronizer;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\DbTestCase;

/**
 * DataSyncronizerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class DataSyncronizerTest extends DbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testSync()
    {
        $config = $this->getConfig('info_title');
        $operation = $this->getOperation('backend.action.execute');
        $schema = $this->getSchema('Passthru');
        $event = $this->getEvent('fusio.app.create');
        $cronjob = $this->getCronjob('Renew_Token');
        $scope = $this->getScope('backend.action');

        DataSyncronizer::sync($this->connection);

        $this->assertEquals($config, $this->getConfig('info_title'));
        $this->assertEquals($operation, $this->getOperation('backend.action.execute'));
        $this->assertEquals($schema, $this->getSchema('Passthru'));
        $this->assertEquals($event, $this->getEvent('fusio.app.create'));
        $this->assertEquals($cronjob, $this->getCronjob('Renew_Token'));
        $this->assertEquals($scope, $this->getScope('backend.action'));
    }

    private function getConfig(string $name): array
    {
        $config = $this->connection->fetchAssociative('SELECT * FROM fusio_config WHERE name = :name', ['name' => $name]);
        if (empty($config)) {
            throw new \RuntimeException('Could not find config: ' . $name);
        }

        $this->connection->delete('fusio_config', ['id' => $config['id']]);
        unset($config['id']);

        return $config;
    }

    private function getOperation(string $name): array
    {
        $operation = $this->connection->fetchAssociative('SELECT * FROM fusio_operation WHERE name = :name', ['name' => $name]);
        if (empty($operation)) {
            throw new \RuntimeException('Could not find operation: ' . $name);
        }

        $this->connection->delete('fusio_scope_operation', ['operation_id' => $operation['id']]);
        $this->connection->delete('fusio_rate_allocation', ['operation_id' => $operation['id']]);
        $this->connection->delete('fusio_operation', ['id' => $operation['id']]);
        unset($operation['id']);

        return $operation;
    }

    private function getSchema(string $name): array
    {
        $schema = $this->connection->fetchAssociative('SELECT * FROM fusio_schema WHERE name = :name', ['name' => $name]);
        if (empty($schema)) {
            throw new \RuntimeException('Could not find schema: ' . $name);
        }

        $this->connection->delete('fusio_schema', ['id' => $schema['id']]);
        unset($schema['id']);

        return $schema;
    }

    private function getEvent(string $name): array
    {
        $event = $this->connection->fetchAssociative('SELECT * FROM fusio_event WHERE name = :name', ['name' => $name]);
        if (empty($event)) {
            throw new \RuntimeException('Could not find event: ' . $name);
        }

        $this->connection->delete('fusio_event', ['id' => $event['id']]);
        unset($event['id']);

        return $event;
    }

    private function getCronjob(string $name): array
    {
        $cronjob = $this->connection->fetchAssociative('SELECT * FROM fusio_cronjob WHERE name = :name', ['name' => $name]);
        if (empty($cronjob)) {
            throw new \RuntimeException('Could not find cronjob: ' . $name);
        }

        $this->connection->delete('fusio_cronjob', ['id' => $cronjob['id']]);
        unset($cronjob['id']);

        return $cronjob;
    }

    private function getScope(string $name): array
    {
        $scope = $this->connection->fetchAssociative('SELECT * FROM fusio_scope WHERE name = :name', ['name' => $name]);
        if (empty($scope)) {
            throw new \RuntimeException('Could not find scope: ' . $name);
        }

        $this->connection->delete('fusio_scope_operation', ['scope_id' => $scope['id']]);
        $this->connection->delete('fusio_scope', ['id' => $scope['id']]);
        unset($scope['id']);

        return $scope;
    }
}

<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Migrations;

use Fusio\Impl\Backend;
use Fusio\Impl\Migrations\DataSyncronizer;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\DbTestCase;

/**
 * DataSyncronizerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class DataSyncronizerTest extends DbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testSync()
    {
        $config = $this->getConfig('info_title');
        $route = $this->getRoute('/backend/action');
        $action = $this->getAction('Backend_Action_Action_Get');
        $schema = $this->getSchema('Backend_Action');
        $event = $this->getEvent('fusio.action.create');
        $cronjob = $this->getCronjob('Dispatch_Event');

        // we delete several entries and check whether the sync inserts those entries
        $this->connection->delete('fusio_config', ['name' => 'info_title']);
        $this->connection->delete('fusio_routes', ['path' => '/backend/action']);
        $this->connection->delete('fusio_action', ['name' => 'Backend_Action_Action_Get']);
        $this->connection->delete('fusio_schema', ['name' => 'Backend_Action']);
        $this->connection->delete('fusio_event', ['name' => 'fusio.action.create']);
        $this->connection->delete('fusio_cronjob', ['name' => 'Dispatch_Event']);

        DataSyncronizer::sync($this->connection);

        $this->assertEquals($config, $this->getConfig('info_title'));
        $this->assertEquals($route, $this->getRoute('/backend/action'));
        $this->assertEquals($action, $this->getAction('Backend_Action_Action_Get'));
        $this->assertEquals($schema, $this->getSchema('Backend_Action'));
        $this->assertEquals($event, $this->getEvent('fusio.action.create'));
        $this->assertEquals($cronjob, $this->getCronjob('Dispatch_Event'));
    }

    private function getConfig(string $name): array
    {
        $config = $this->connection->fetchAssociative('SELECT * FROM fusio_config WHERE name = :name', ['name' => $name]);
        unset($config['id']);

        return $config;
    }

    private function getRoute(string $path): array
    {
        $route = $this->connection->fetchAssociative('SELECT * FROM fusio_routes WHERE path = :path', ['path' => $path]);
        $route['methods'] = $this->getMethods((int) $route['id']);
        unset($route['id']);

        return $route;
    }

    private function getMethods(int $routeId): array
    {
        $result = [];
        $methods = $this->connection->fetchAllAssociative('SELECT * FROM fusio_routes_method WHERE route_id = :route_id', ['route_id' => $routeId]);
        foreach ($methods as $method) {
            $method['responses'] = $this->getResponses($method['id']);
            unset($method['id']);
            unset($method['route_id']);
            $result[] = $method;
        }

        return $result;
    }

    private function getResponses(int $methodId): array
    {
        $result = [];
        $responses = $this->connection->fetchAllAssociative('SELECT * FROM fusio_routes_response WHERE method_id = :method_id', ['method_id' => $methodId]);
        foreach ($responses as $response) {
            unset($response['id']);
            unset($response['method_id']);
            $result[] = $response;
        }

        return $result;
    }

    private function getAction(string $name): array
    {
        $action = $this->connection->fetchAssociative('SELECT * FROM fusio_action WHERE name = :name', ['name' => $name]);
        unset($action['id']);

        return $action;
    }

    private function getSchema(string $name): array
    {
        $schema = $this->connection->fetchAssociative('SELECT * FROM fusio_schema WHERE name = :name', ['name' => $name]);
        unset($schema['id']);

        return $schema;
    }

    private function getEvent(string $name): array
    {
        $event = $this->connection->fetchAssociative('SELECT * FROM fusio_event WHERE name = :name', ['name' => $name]);
        unset($event['id']);

        return $event;
    }

    private function getCronjob(string $name): array
    {
        $cronjob = $this->connection->fetchAssociative('SELECT * FROM fusio_cronjob WHERE name = :name', ['name' => $name]);
        unset($cronjob['id']);

        return $cronjob;
    }
}
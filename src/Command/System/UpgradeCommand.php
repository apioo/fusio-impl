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

namespace Fusio\Impl\Command\System;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Action\Scheme as ActionScheme;
use Fusio\Impl\Framework\Schema\Scheme as SchemaScheme;
use Fusio\Impl\Table;
use PSX\Api\OperationInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * UpgradeCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UpgradeCommand extends Command
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    protected function configure(): void
    {
        $this
            ->setName('system:upgrade')
            ->setAliases(['upgrade'])
            ->setDescription('Upgrades an existing 3.x database structure to 4.x');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->connection->createSchemaManager()->tablesExist(['fusio_routes'])) {
            $output->writeln('It looks like the database is already upgraded, found no legacy fusio_routes table');
            return self::SUCCESS;
        }

        $count = (int) $this->connection->fetchOne('SELECT COUNT(*) AS cnt FROM fusio_routes');
        if ($count === 0) {
            $output->writeln('It looks like the database is already upgraded, the legacy fusio_routes table is empty');
            return self::SUCCESS;
        }

        $output->writeln('Starting upgrade ...');

        $this->runFusio3xMigration($output);

        return self::SUCCESS;
    }

    private function runFusio3xMigration(OutputInterface $output): void
    {
        $operationRouteMap = [];

        $routes = $this->connection->fetchAllAssociative('SELECT * FROM fusio_routes WHERE category_id NOT IN (2, 3, 4, 5) ORDER BY id ASC');
        foreach ($routes as $route) {
            $methods = $this->connection->fetchAllAssociative('SELECT * FROM fusio_routes_method WHERE route_id = :route_id ORDER BY id ASC', ['route_id' => $route['id']]);
            foreach ($methods as $method) {
                $httpCode = 200;
                $outgoing = 'Passthru';
                $responses = $this->connection->fetchAllAssociative('SELECT * FROM fusio_routes_response WHERE method_id = :method_id ORDER BY id ASC', ['method_id' => $method['id']]);
                foreach ($responses as $response) {
                    $code = (int) $response['code'];
                    if ($code >= 200 && $code < 300) {
                        $httpCode = $code;
                        $outgoing = $response['response'];
                    }
                }

                $operationId = $method['operation_id'];
                if (empty($operationId)) {
                    $operationId = $this->guessOperationId($method['method'], $route['path']);
                }

                $existing = $this->connection->fetchAssociative('SELECT * FROM fusio_operation WHERE (http_method = :method AND http_path = :path) OR name = :name', [
                    'method' => $method['method'],
                    'path' => $route['path'],
                    'name' => $operationId,
                ]);

                if (!empty($existing)) {
                    $output->writeln('Operation already exists ' . $operationId . ' (' . $method['method'] . '-' . $route['path'] . ')');
                    continue;
                }

                $action = $method['action'];
                if (empty($action)) {
                    $output->writeln('Skip route method with empty action ' . $operationId . ' (' . $method['method'] . '-' . $route['path'] . ')');
                    continue;
                }

                $this->connection->insert('fusio_operation', [
                    'category_id' => $route['category_id'],
                    'status' => Table\Operation::STATUS_ACTIVE,
                    'active' => $method['active'],
                    'public' => $method['public'],
                    'stability' => OperationInterface::STABILITY_EXPERIMENTAL,
                    'description' => $method['description'],
                    'http_method' => $method['method'],
                    'http_path' => $route['path'],
                    'http_code' => $httpCode,
                    'name' => $operationId,
                    'parameters' => '',
                    'incoming' => SchemaScheme::wrap($method['request']),
                    'outgoing' => SchemaScheme::wrap($outgoing),
                    'throws' => '',
                    'action' => ActionScheme::wrap($action),
                    'costs' => $method['costs'],
                    'metadata' => $route['metadata'],
                ]);

                $operationId = (int) $this->connection->lastInsertId();
                $operationRouteMap[$operationId] = $route['id'];

                $output->writeln('Added operation ' . $operationId . ' (' . $method['method'] . '-' . $route['path'] . ')');
            }
        }

        $operationScopeName = 'backend.operation';
        $operationScopeId = (int) $this->connection->fetchOne('SELECT id FROM fusio_scope WHERE name = :name', ['name' => $operationScopeName]);
        if (empty($operationScopeId)) {
            $this->connection->insert('fusio_scope', [
                'category_id' => 2,
                'status' => 1,
                'name' => 'backend.operation',
                'description' => '',
            ]);
            $operationScopeId = (int) $this->connection->lastInsertId();

            $output->writeln('Added backend operation scope ' . $operationScopeId);
        }

        $operationIds = $this->connection->fetchFirstColumn('SELECT id FROM fusio_operation WHERE name LIKE :name', ['name' => 'backend.operation.%']);
        foreach ($operationIds as $operationId) {
            $scopeOperationId = $this->connection->fetchOne('SELECT id FROM fusio_scope_operation WHERE scope_id = :scope_id AND operation_id = :operation_id', [
                'scope_id' => $operationScopeId,
                'operation_id' => $operationId,
            ]);

            if (empty($scopeOperationId)) {
                $this->connection->insert('fusio_scope_operation', [
                    'scope_id' => $operationScopeId,
                    'operation_id' => $operationId,
                    'allow' => 1,
                ]);

                $output->writeln('Assigned operation ' . $operationId . ' to backend operation scope');
            }
        }

        $result = $this->connection->fetchAllAssociative('SELECT * FROM fusio_scope_routes ORDER BY id ASC');
        foreach ($result as $row) {
            $operationIds = [];
            foreach ($operationRouteMap as $operationId => $routeId) {
                if ($routeId == $row['route_id']) {
                    $operationIds[] = $operationId;
                }
            }

            foreach ($operationIds as $operationId) {
                $this->connection->insert('fusio_scope_operation', [
                    'scope_id' => $row['scope_id'],
                    'operation_id' => $operationId,
                    'allow' => 1,
                ]);
            }

            $this->connection->delete('fusio_scope_routes', ['id' => $row['id']]);

            $output->writeln('Migrated scope ' . $row['scope_id'] . ' to operation');
        }

        $this->connection->executeStatement('DELETE FROM fusio_routes_response WHERE 1=1');
        $output->writeln('Truncated table fusio_routes_response');

        $this->connection->executeStatement('DELETE FROM fusio_routes_method WHERE 1=1');
        $output->writeln('Truncated table fusio_routes_method');

        $this->connection->executeStatement('DELETE FROM fusio_routes WHERE 1=1');
        $output->writeln('Truncated table fusio_routes');
    }

    private function guessOperationId(string $httpMethod, string $httpPath): string
    {
        $parts = array_filter(explode('/', $httpPath));

        $parts = array_map(static function(string $part){
            if ($part[0] === ':') {
                return substr($part, 1);
            } elseif ($part[0] === '$') {
                $pos = strpos($part, '<');
                if ($pos !== false) {
                    return substr($part, 1, $pos - 1);
                } else {
                    return substr($part, 1);
                }
            } else {
                return $part;
            }
        }, $parts);

        return strtolower($httpMethod) . '.' . implode('.', $parts);
    }
}

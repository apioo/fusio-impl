<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Service;

use Doctrine\DBAL\Connection;
use Fusio\Engine\ContextInterface;
use Fusio\Impl\Installation\NewInstallation;
use Fusio\Impl\Installation\Reference;
use PSX\Http\Exception\BadRequestException;

/**
 * Tenant
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Tenant
{
    public const TENANT_TABLES = [
        'fusio_role',
        'fusio_config',
        'fusio_connection',
        'fusio_cronjob',
        'fusio_action',
        'fusio_test',
        'fusio_operation',
        'fusio_page',
        'fusio_schema',
        'fusio_transaction',
        'fusio_rate',
        'fusio_plan',
        'fusio_scope',
        'fusio_app',
        'fusio_audit',
        'fusio_event',
        'fusio_identity',
        'fusio_log',
        'fusio_user',
        'fusio_token',
        'fusio_webhook',
        'fusio_category',
    ];

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function setup(string $tenantId, ContextInterface $context): void
    {
        if (!empty($context->getTenantId())) {
            throw new BadRequestException('Tenant operations are only allowed at the root tenant');
        }

        if (!preg_match('/^[A-Za-z0-9_]{3,64}$/', $tenantId)) {
            throw new BadRequestException('Provided tenant must be in the format: [A-Za-z0-9_]{3,64}');
        }

        $count = (int) $this->connection->fetchOne('SELECT COUNT(*) AS cnt FROM fusio_config WHERE tenant_id = :tenant_id', [
            'tenant_id' => $tenantId,
        ]);

        if ($count > 0) {
            throw new BadRequestException('Provided tenant is already configured');
        }

        $inserts = NewInstallation::getData($tenantId)->toArray();
        foreach ($inserts as $tableName => $rows) {
            foreach ($rows as $row) {
                foreach ($row as $key => $value) {
                    if ($value instanceof Reference) {
                        $row[$key] = $value->resolve($this->connection);
                    }
                }

                $this->connection->insert($tableName, $row);
            }
        }
    }

    public function remove(string $tenantId, ContextInterface $context): void
    {
        if (!empty($context->getTenantId())) {
            throw new BadRequestException('Tenant operations are only allowed at the root tenant');
        }

        $relations = [
            'fusio_app' => ['fusio_app_scope' => 'app_id'],
            'fusio_cronjob' => ['fusio_cronjob_error' => 'cronjob_id'],
            'fusio_log' => ['fusio_log_error' => 'log_id'],
            'fusio_operation' => ['fusio_plan_usage' => 'operation_id', 'fusio_scope_operation' => 'operation_id'],
            'fusio_plan' => ['fusio_plan_scope' => 'plan_id'],
            'fusio_role' => ['fusio_role_scope' => 'role_id'],
            'fusio_rate' => ['fusio_rate_allocation' => 'rate_id'],
            'fusio_scope' => ['fusio_scope_operation' => 'scope_id', 'fusio_plan_scope' => 'scope_id', 'fusio_role_scope' => 'scope_id', 'fusio_user_scope' => 'scope_id', 'fusio_app_scope' => 'scope_id'],
            'fusio_user' => ['fusio_user_grant' => 'user_id', 'fusio_user_scope' => 'user_id'],
        ];

        foreach (self::TENANT_TABLES as $tableName) {
            if (isset($relations[$tableName])) {
                $result = $this->connection->fetchAllAssociative('SELECT id FROM ' . $tableName . ' WHERE tenant_id = :tenant_id', ['tenant_id' => $tenantId]);
                foreach ($result as $row) {
                    foreach ($relations[$tableName] as $foreignTable => $column) {
                        $this->connection->delete($foreignTable, [$column => $row['id']]);
                    }
                }
            }

            $this->connection->delete($tableName, ['tenant_id' => $tenantId]);
        }
    }
}

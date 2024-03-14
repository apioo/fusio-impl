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
        'fusio_category',
        'fusio_config',
        'fusio_connection',
        'fusio_cronjob',
        'fusio_action',
        'fusio_operation',
        'fusio_page',
        'fusio_schema',
        'fusio_transaction',
        'fusio_role',
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

        foreach (self::TENANT_TABLES as $tableName) {
            if ($tableName === 'fusio_operation') {
                $result = $this->connection->fetchAllAssociative('SELECT id FROM fusio_operation WHERE tenant_id = :tenant_id', ['tenant_id' => $tenantId]);
                foreach ($result as $row) {
                    $this->connection->delete('fusio_plan_usage', ['operation_id' => $row['id']]);
                }
            } elseif ($tableName === 'fusio_cronjob') {
                $result = $this->connection->fetchAllAssociative('SELECT id FROM fusio_cronjob WHERE tenant_id = :tenant_id', ['tenant_id' => $tenantId]);
                foreach ($result as $row) {
                    $this->connection->delete('fusio_cronjob_error', ['cronjob_id' => $row['id']]);
                }
            } elseif ($tableName === 'fusio_event') {
            } elseif ($tableName === 'fusio_role') {
                $result = $this->connection->fetchAllAssociative('SELECT id FROM fusio_role WHERE tenant_id = :tenant_id', ['tenant_id' => $tenantId]);
                foreach ($result as $row) {
                    $this->connection->delete('fusio_role_scope', ['role_id' => $row['id']]);
                }
            } elseif ($tableName === 'fusio_rate') {
                $result = $this->connection->fetchAllAssociative('SELECT id FROM fusio_rate WHERE tenant_id = :tenant_id', ['tenant_id' => $tenantId]);
                foreach ($result as $row) {
                    $this->connection->delete('fusio_rate_allocation', ['rate_id' => $row['id']]);
                }
            } elseif ($tableName === 'fusio_plan') {
                $result = $this->connection->fetchAllAssociative('SELECT id FROM fusio_plan WHERE tenant_id = :tenant_id', ['tenant_id' => $tenantId]);
                foreach ($result as $row) {
                    $this->connection->delete('fusio_plan_scope', ['plan_id' => $row['id']]);
                }
            } elseif ($tableName === 'fusio_user') {
                $result = $this->connection->fetchAllAssociative('SELECT id FROM fusio_user WHERE tenant_id = :tenant_id', ['tenant_id' => $tenantId]);
                foreach ($result as $row) {
                    $this->connection->delete('fusio_user_grant', ['user_id' => $row['id']]);
                    $this->connection->delete('fusio_user_scope', ['user_id' => $row['id']]);
                }
            } elseif ($tableName === 'fusio_scope') {
                $result = $this->connection->fetchAllAssociative('SELECT id FROM fusio_scope WHERE tenant_id = :tenant_id', ['tenant_id' => $tenantId]);
                foreach ($result as $row) {
                    $this->connection->delete('fusio_scope_operation', ['scope_id' => $row['id']]);
                }
            }

            $this->connection->delete($tableName, ['tenant_id' => $tenantId]);
        }
    }
}

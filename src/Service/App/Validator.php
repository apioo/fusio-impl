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

namespace Fusio\Impl\Service\App;

use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\App;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\App $appTable;
    private Table\User $userTable;
    private UsageLimiter $usageLimiter;

    public function __construct(Table\App $appTable, Table\User $userTable, UsageLimiter $usageLimiter)
    {
        $this->appTable = $appTable;
        $this->userTable = $userTable;
        $this->usageLimiter = $usageLimiter;
    }

    public function assert(App $app, ?string $tenantId, ?Table\Generated\AppRow $existing = null): void
    {
        $this->usageLimiter->assertAppCount($tenantId);

        $userId = $app->getUserId();
        if ($userId !== null) {
            $this->assertUser($userId, $tenantId);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('App user must not be empty');
            }

            $userId = $existing->getUserId();
        }

        $name = $app->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $userId, $existing);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('App name must not be empty');
            }
        }
    }

    private function assertUser(int $userId, ?string $tenantId): void
    {
        $user = $this->userTable->findOneByTenantAndId($tenantId, $userId);
        if (empty($user)) {
            throw new StatusCode\BadRequestException('Provided user id does not exist');
        }
    }

    private function assertName(string $name, ?string $tenantId, int $userId, ?Table\Generated\AppRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid action name');
        }

        if ($existing === null || $name !== $existing->getName()) {
            $condition  = Condition::withAnd();
            $condition->equals(Table\Generated\AppTable::COLUMN_TENANT_ID, $tenantId);
            $condition->equals(Table\Generated\AppTable::COLUMN_USER_ID, $userId);
            $condition->notEquals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_DELETED);
            $condition->equals(Table\Generated\AppTable::COLUMN_NAME, $name);

            $existing = $this->appTable->findOneBy($condition);
            if (!empty($existing)) {
                throw new StatusCode\BadRequestException('App already exists');
            }
        }
    }
}

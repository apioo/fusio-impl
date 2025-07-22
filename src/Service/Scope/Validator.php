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

namespace Fusio\Impl\Service\Scope;

use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Scope;
use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Validator
{
    public function __construct(
        private Table\Scope $scopeTable,
        private UsageLimiter $usageLimiter
    ) {
    }

    public function assert(Scope $scope, ?string $tenantId, ?Table\Generated\ScopeRow $existing = null): void
    {
        $this->usageLimiter->assertScopeCount($tenantId);

        $name = $scope->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Scope name must not be empty');
            }
        }

        if ($existing !== null) {
            // check whether this is a system scope
            if (in_array($existing->getId(), [1, 2, 3])) {
                throw new StatusCode\BadRequestException('It is not possible to change this scope');
            }
        }
    }

    private function assertName(string $name, ?string $tenantId, ?Table\Generated\ScopeRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_\\.]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid scope name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->scopeTable->findOneByTenantAndName($tenantId, null, $name)) {
            throw new StatusCode\BadRequestException('Scope already exists');
        }
    }
}

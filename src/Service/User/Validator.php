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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Service;
use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\User;
use Fusio\Model\Backend\UserCreate;
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
        private Table\User $userTable,
        private Table\Role $roleTable,
        private Table\Plan $planTable,
        private Service\Config $configService,
        private UsageLimiter $usageLimiter,
    ) {
    }

    public function assert(User $user, ?string $tenantId, ?Table\Generated\UserRow $existing = null): void
    {
        $this->usageLimiter->assertUserCount($tenantId);

        $roleId = $user->getRoleId();
        if ($roleId !== null) {
            $this->assertRoleId($roleId, $tenantId);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('User role id must not be empty');
            }
        }

        $planId = $user->getPlanId();
        if ($planId !== null) {
            $this->assertPlanId($planId, $tenantId);
        }

        $name = $user->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('User name must not be empty');
            }
        }

        $email = $user->getEmail();
        if ($email !== null) {
            $this->assertEmail($email, $tenantId, $existing);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('User email must not be empty');
            }
        }

        if ($user instanceof UserCreate) {
            $password = $user->getPassword();
            if ($password !== null) {
                $this->assertPassword($password, $this->configService->getValue('user_pw_length'));
            } else {
                if ($existing === null) {
                    throw new StatusCode\BadRequestException('User password must not be empty');
                }
            }
        }
    }

    public function assertName(?string $name, ?string $tenantId, ?Table\Generated\UserRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_\\.]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid user name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->userTable->findOneByTenantAndName($tenantId, $name)) {
            throw new StatusCode\BadRequestException('User name already exists');
        }
    }

    public function assertEmail(?string $email, ?string $tenantId, ?Table\Generated\UserRow $existing = null): void
    {
        if (empty($email)) {
            throw new StatusCode\BadRequestException('Email must not be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new StatusCode\BadRequestException('Invalid email format');
        }

        if (($existing === null || $email !== $existing->getEmail()) && $this->userTable->findOneByTenantAndEmail($tenantId, $email)) {
            throw new StatusCode\BadRequestException('User email already exists');
        }
    }

    public function assertPassword($password, $minLength = null, $minAlpha = null, $minNumeric = null, $minSpecial = null): void
    {
        if (empty($password)) {
            throw new StatusCode\BadRequestException('Password must not be empty');
        }

        $minLength  = $minLength ?? $this->configService->getValue('user_pw_length');
        $minAlpha   = $minAlpha ?? 0;
        $minNumeric = $minNumeric ?? 0;
        $minSpecial = $minSpecial ?? 0;

        // it is not possible to use passwords which have less than 8 chars
        if ($minLength < 8) {
            $minLength = 8;
        }

        $len     = strlen($password);
        $alpha   = 0;
        $numeric = 0;
        $special = 0;

        if ($len < $minLength) {
            throw new StatusCode\BadRequestException('Password must have at least ' . $minLength . ' characters');
        }

        for ($i = 0; $i < $len; $i++) {
            $value = ord($password[$i]);
            if ($value >= 0x21 && $value <= 0x7E) {
                if ($value >= 0x30 && $value <= 0x39) {
                    $numeric++;
                } elseif ($value >= 0x41 && $value <= 0x5A) {
                    $alpha++;
                } elseif ($value >= 0x61 && $value <= 0x7A) {
                    $alpha++;
                } else {
                    $special++;
                }
            } else {
                throw new StatusCode\BadRequestException('Password must contain only printable ascii characters (0x21-0x7E)');
            }
        }

        if ($alpha < $minAlpha) {
            throw new StatusCode\BadRequestException('Password must have at least ' . $minAlpha . ' alphabetic character (a-z, A-Z)');
        }

        if ($numeric < $minNumeric) {
            throw new StatusCode\BadRequestException('Password must have at least ' . $minNumeric . ' numeric character (0-9)');
        }

        if ($special < $minSpecial) {
            throw new StatusCode\BadRequestException('Password must have at least ' . $minSpecial . ' special character i.e. (!#$%&*@_~)');
        }
    }

    private function assertRoleId(int $roleId, ?string $tenantId): void
    {
        $role = $this->roleTable->findOneByTenantAndId($tenantId, $roleId);
        if (!$role instanceof Table\Generated\RoleRow) {
            throw new StatusCode\BadRequestException('Provided role id does not exist');
        }
    }

    private function assertPlanId(int $planId, ?string $tenantId): void
    {
        $plan = $this->planTable->findOneByTenantAndId($tenantId, $planId);
        if (!$plan instanceof Table\Generated\PlanRow) {
            throw new StatusCode\BadRequestException('Provided plan id does not exist');
        }
    }
}

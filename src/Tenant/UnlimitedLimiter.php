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

namespace Fusio\Impl\Tenant;

use Fusio\Impl\Service\Tenant\LimiterInterface;

/**
 * UnlimitedLimiter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UnlimitedLimiter implements LimiterInterface
{
    public function getActionCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getAppCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getCategoryCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getConnectionCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getCronjobCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getEventCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getFirewallCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getFormCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getIdentityCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getOperationCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getPageCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getPlanCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getRateCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getRoleCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getSchemaCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getScopeCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getUserCount(): int
    {
        return PHP_INT_MAX;
    }

    public function getWebhookCount(): int
    {
        return PHP_INT_MAX;
    }
}

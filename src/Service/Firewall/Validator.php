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

namespace Fusio\Impl\Service\Firewall;

use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Firewall;
use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\Firewall $firewallTable;
    private UsageLimiter $usageLimiter;

    public function __construct(Table\Firewall $firewallTable, UsageLimiter $usageLimiter)
    {
        $this->firewallTable = $firewallTable;
        $this->usageLimiter = $usageLimiter;
    }

    public function assert(Firewall $firewall, ?string $tenantId, ?Table\Generated\FirewallRow $existing = null): void
    {
        $this->usageLimiter->assertFirewallCount($tenantId);

        $name = $firewall->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Firewall name must not be empty');
        }

        $ip = $firewall->getIp();
        if ($ip !== null) {
            $this->assertIP($ip);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Firewall IP must not be empty');
        }
    }

    private function assertName(string $name, ?string $tenantId, ?Table\Generated\FirewallRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_\\.]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid firewall name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->firewallTable->findOneByTenantAndName($tenantId, $name)) {
            throw new StatusCode\BadRequestException('Firewall already exists');
        }
    }

    private function assertIP(string $ip): void
    {
        if (inet_pton($ip) === false) {
            throw new StatusCode\BadRequestException('Firewall IP must be a valid IPv4 or IPv6');
        }
    }
}

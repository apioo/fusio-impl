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

namespace Fusio\Impl\Service\Bundle;

use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Bundle;
use Fusio\Model\Backend\BundleConfig;
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
    private const ALLOWED_KEYS = ['actions', 'schemas', 'events', 'cronjobs', 'triggers'];

    public function __construct(
        private Table\Bundle $bundleTable,
        private UsageLimiter $usageLimiter
    ) {
    }

    public function assert(Bundle $bundle, ?string $tenantId, ?Table\Generated\BundleRow $existing = null): void
    {
        $this->usageLimiter->assertBundleCount($tenantId);

        $name = $bundle->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Bundle name must not be empty');
        }

        $config = $bundle->getConfig();
        if ($config !== null) {
            $this->assertConfig($config, $tenantId, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Bundle config must not be empty');
        }
    }

    private function assertName(string $name, ?string $tenantId, ?Table\Generated\BundleRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid bundle name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->bundleTable->findOneByTenantAndName($tenantId, $name)) {
            throw new StatusCode\BadRequestException('Bundle already exists');
        }
    }

    private function assertConfig(BundleConfig $config, ?string $tenantId, ?Table\Generated\BundleRow $existing = null): void
    {
        if ($config->isEmpty()) {
            throw new StatusCode\BadRequestException('Bundle config must not be empty');
        }

        $count = 0;
        foreach ($config->getAll() as $key => $entries) {
            if (!in_array($key, self::ALLOWED_KEYS, true)) {
                throw new StatusCode\BadRequestException('Provided an invalid config key ' . $key . ' must be one of: ' . implode(', ', self::ALLOWED_KEYS));
            }

            if (!is_array($entries)) {
                throw new StatusCode\BadRequestException('Bundle config entries for "' . $key . '" must be of type array');
            }

            foreach ($entries as $index => $entry) {
                if (!is_string($entry)) {
                    throw new StatusCode\BadRequestException('Bundle config entry for "' . $key . '" at index "' . $index . '" must be of type string');
                }

                $count++;
            }
        }

        if ($count === 0) {
            throw new StatusCode\BadRequestException('Bundle config must have at least one entry');
        }
    }
}

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

namespace Fusio\Impl\Service\Cronjob;

use Cron\CronExpression;
use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Cronjob;
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
    private Table\Cronjob $cronjobTable;
    private UsageLimiter $usageLimiter;

    public function __construct(Table\Cronjob $cronjobTable, UsageLimiter $usageLimiter)
    {
        $this->cronjobTable = $cronjobTable;
        $this->usageLimiter = $usageLimiter;
    }

    public function assert(Cronjob $cronjob, ?string $tenantId, ?Table\Generated\CronjobRow $existing = null): void
    {
        $this->usageLimiter->assertCronjobCount($tenantId);

        $name = $cronjob->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Cronjob name must not be empty');
            }
        }

        $cron = $cronjob->getCron();
        if ($cron !== null) {
            $this->assertCron($cron);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Cronjob expression must not be empty');
            }
        }
    }

    private function assertName(string $name, ?string $tenantId, ?Table\Generated\CronjobRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid connection name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->cronjobTable->findOneByTenantAndName($tenantId, null, $name)) {
            throw new StatusCode\BadRequestException('Connection already exists');
        }
    }

    private function assertCron(string $cron): void
    {
        if (empty($cron)) {
            throw new StatusCode\BadRequestException('Cron must not be empty');
        }

        try {
            new CronExpression($cron);
        } catch (\InvalidArgumentException $e) {
            throw new StatusCode\BadRequestException($e->getMessage(), $e);
        }
    }
}

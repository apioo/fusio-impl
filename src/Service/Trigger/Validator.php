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

namespace Fusio\Impl\Service\Trigger;

use Fusio\Engine\Exception\ActionNotFoundException;
use Fusio\Engine\Exception\FactoryResolveException;
use Fusio\Engine\Processor;
use Fusio\Impl\Action\Scheme as ActionScheme;
use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Trigger;
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
        private Table\Trigger $triggerTable,
        private Table\Action $actionTable,
        private Processor $processor,
        private UsageLimiter $usageLimiter
    ) {
    }

    public function assert(Trigger $trigger, int $categoryId, ?string $tenantId, ?Table\Generated\TriggerRow $existing = null): void
    {
        $this->usageLimiter->assertTriggerCount($tenantId);

        $name = $trigger->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Trigger name must not be empty');
            }
        }

        $action = $trigger->getAction();
        if ($action !== null) {
            $this->assertAction($action, $categoryId, $tenantId);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Action must not be empty');
            }
        }
    }

    private function assertName(string $name, ?string $tenantId, ?Table\Generated\TriggerRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid connection name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->triggerTable->findOneByTenantAndName($tenantId, null, $name)) {
            throw new StatusCode\BadRequestException('Trigger already exists');
        }
    }

    private function assertAction(string $action, int $categoryId, ?string $tenantId): void
    {
        $scheme = ActionScheme::wrap($action);
        if (empty($scheme)) {
            throw new StatusCode\BadRequestException('Action no value provided, you need to provide an existing action name as value');
        }

        if (str_starts_with($scheme, 'action://')) {
            $row = $this->actionTable->findOneByTenantAndName($tenantId, $categoryId, substr($scheme, 9));
            if (!$row instanceof Table\Generated\ActionRow) {
                throw new StatusCode\BadRequestException('Action "' . $action . '" does not exist, you need to provide an existing action name as value');
            }
        }

        try {
            $this->processor->getAction($scheme);
        } catch (ActionNotFoundException|FactoryResolveException $e) {
            throw new StatusCode\BadRequestException('Action "' . $action . '" does not exist', $e);
        }
    }
}

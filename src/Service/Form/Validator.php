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

namespace Fusio\Impl\Service\Form;

use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Form;
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
        private Table\Form $formTable,
        private Table\Operation $operationTable,
        private UsageLimiter $usageLimiter
    ) {
    }

    public function assert(Form $form, ?string $tenantId, ?Table\Generated\FormRow $existing = null): void
    {
        $this->usageLimiter->assertFormCount($tenantId);

        $name = $form->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Form name must not be empty');
        }

        $operationId = $form->getOperationId();
        if ($operationId !== null) {
            $this->assertOperationId($operationId, $tenantId);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('Form operation id must not be empty');
            }
        }
    }

    private function assertName(string $name, ?string $tenantId, ?Table\Generated\FormRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_\\.]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid form name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->formTable->findOneByTenantAndName($tenantId, $name)) {
            throw new StatusCode\BadRequestException('Form already exists');
        }
    }

    private function assertOperationId(int $operationId, ?string $tenantId): void
    {
        $operation = $this->operationTable->findOneByTenantAndId($tenantId, null, $operationId);
        if (!$operation instanceof Table\Generated\OperationRow) {
            throw new StatusCode\BadRequestException('Provided operation id does not exist');
        }
    }
}

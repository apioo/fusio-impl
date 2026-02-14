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

namespace Fusio\Impl\Service\Taxonomy;

use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Impl\Table\Generated\TaxonomyRow;
use Fusio\Model\Backend\Taxonomy;
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
        private Table\Taxonomy $taxonomyTable,
        private UsageLimiter $usageLimiter
    ) {
    }

    public function assert(Taxonomy $taxonomy, ?string $tenantId, ?Table\Generated\TaxonomyRow $existing = null): void
    {
        $this->usageLimiter->assertTaxonomyCount($tenantId);

        $parentId = $taxonomy->getParentId();
        if ($parentId !== null) {
            $this->assertParentId($parentId, $tenantId, $existing);
        }

        $name = $taxonomy->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Taxonomy name must not be empty');
        }
    }

    private function assertParentId(int $parentId, ?string $tenantId, ?Table\Generated\TaxonomyRow $existing = null): void
    {
        $row = $this->taxonomyTable->findOneByTenantAndId($tenantId, $parentId);
        if (!$row instanceof TaxonomyRow) {
            throw new StatusCode\BadRequestException('Parent taxonomy does not exist');
        }

        if ($existing instanceof TaxonomyRow) {
            if ($row->getId() === $existing->getId()) {
                throw new StatusCode\BadRequestException('Parent taxonomy must be different');
            }
        }
    }

    private function assertName(string $name, ?string $tenantId, ?Table\Generated\TaxonomyRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid taxonomy name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->taxonomyTable->findOneByTenantAndName($tenantId, $name)) {
            throw new StatusCode\BadRequestException('Taxonomy already exists');
        }
    }
}

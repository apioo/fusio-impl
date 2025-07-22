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

namespace Fusio\Impl\Service\Category;

use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Category;
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
        private Table\Category $categoryTable,
        private UsageLimiter $usageLimiter
    ) {
    }

    public function assert(Category $category, ?string $tenantId, ?Table\Generated\CategoryRow $existing = null): void
    {
        $this->usageLimiter->assertCategoryCount($tenantId);

        $name = $category->getName();
        if ($name !== null) {
            $this->assertName($name, $tenantId, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Category name must not be empty');
        }
    }

    private function assertName(string $name, ?string $tenantId, ?Table\Generated\CategoryRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid category name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->categoryTable->findOneByTenantAndName($tenantId, $name)) {
            throw new StatusCode\BadRequestException('Category already exists');
        }
    }
}

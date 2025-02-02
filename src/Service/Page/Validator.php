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

namespace Fusio\Impl\Service\Page;

use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Page;
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
    private Table\Page $pageTable;
    private UsageLimiter $usageLimiter;

    public function __construct(Table\Page $pageTable, UsageLimiter $usageLimiter)
    {
        $this->pageTable = $pageTable;
        $this->usageLimiter = $usageLimiter;
    }

    public function assert(Page $page, ?string $tenantId, ?Table\Generated\PageRow $existing = null): void
    {
        $this->usageLimiter->assertPageCount($tenantId);

        $title = $page->getTitle();
        if ($title !== null) {
            $this->assertTitle($title, $tenantId, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Page title must not be empty');
        }

        $status = $page->getStatus();
        if ($status !== null) {
            $this->assertStatus($status);
        }
    }

    private function assertTitle(string $title, ?string $tenantId, ?Table\Generated\PageRow $existing = null): void
    {
        if (empty($title)) {
            throw new StatusCode\BadRequestException('Invalid page title');
        }

        if (($existing === null || $title !== $existing->getTitle()) && $this->pageTable->findOneByTenantAndSlug($tenantId, SlugBuilder::build($title))) {
            throw new StatusCode\BadRequestException('Page already exists');
        }
    }

    private function assertStatus(int $status): void
    {
        if (!in_array($status, [Table\Page::STATUS_VISIBLE, Table\Page::STATUS_INVISIBLE])) {
            throw new StatusCode\GoneException('Page status must be either 1 or 2');
        }
    }
}

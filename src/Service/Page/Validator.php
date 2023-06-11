<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Service\Page;

use Fusio\Impl\Table;
use Fusio\Model\Backend\Page;
use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\Page $pageTable;

    public function __construct(Table\Page $pageTable)
    {
        $this->pageTable = $pageTable;
    }

    public function assert(Page $page, ?Table\Generated\PageRow $existing = null): void
    {
        $title = $page->getTitle();
        if ($title !== null) {
            $this->assertTitle($title, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Page title must not be empty');
        }

        $status = $page->getStatus();
        if ($status !== null) {
            $this->assertStatus($status);
        }
    }

    private function assertTitle(string $title, ?Table\Generated\PageRow $existing = null): void
    {
        if (empty($title)) {
            throw new StatusCode\BadRequestException('Invalid page title');
        }

        if (($existing === null || $title !== $existing->getTitle()) && $this->pageTable->findOneByTitle($title)) {
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

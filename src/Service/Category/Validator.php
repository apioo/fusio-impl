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

namespace Fusio\Impl\Service\Category;

use Fusio\Impl\Table;
use Fusio\Model\Backend\Category;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\Category $categoryTable;

    public function __construct(Table\Category $categoryTable)
    {
        $this->categoryTable = $categoryTable;
    }

    public function assert(Category $category, ?Table\Generated\CategoryRow $existing = null): void
    {
        $name = $category->getName();
        if ($name !== null) {
            $this->assertName($name, $existing);
        } elseif ($existing === null) {
            throw new StatusCode\BadRequestException('Category name must not be empty');
        }
    }

    private function assertName(string $name, ?Table\Generated\CategoryRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,255}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid category name');
        }

        if (($existing === null || $name !== $existing->getName()) && $this->categoryTable->findOneByName($name)) {
            throw new StatusCode\BadRequestException('Category already exists');
        }
    }
}